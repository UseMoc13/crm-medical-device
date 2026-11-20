<?php

namespace App\Http\Controllers;

use App\Services\CrmNavigationAccess;
use App\Services\CrmReportingDataScope;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reports (read-only).
 *
 * Definisi:
 *  - Invoiced     = invoice berstatus Sent + Paid (Draft & Cancelled tidak dihitung)
 *  - Paid         = invoice berstatus Paid
 *  - Outstanding  = invoice berstatus Sent
 *  - Overdue      = invoice Sent yang due_date-nya sudah lewat
 *
 * Catatan: tabel invoices belum memiliki tanggal pembayaran, sehingga
 * nilai "Paid" per bulan dikelompokkan berdasarkan tanggal invoice.
 */
class ReportController extends Controller
{
    private const ISSUED = ['Sent', 'Paid'];

    public function index(Request $request): View
    {
        $this->assertReportsView();

        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        [$from, $to] = $this->period($request);

        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();
        $today = today()->toDateString();

        // A7.6.5.7: permission check precedes sensitive customer/product queries.
        $reportPrivacy = app(\App\Services\CrmReportPrivacyPilot::class)
            ->forViewer(Auth::guard('web')->user());
        // A7.6.5.8: group-level opt-in guards. All decisions precede business queries.
        $aggregatePrivacy = app(\App\Services\CrmReportAggregatePrivacyPilot::class)
            ->forViewer(Auth::guard('web')->user());
        $canViewFinancial = $aggregatePrivacy['financial'];
        $canViewOperations = $aggregatePrivacy['operations'];
        $canViewPipeline = $aggregatePrivacy['pipeline'];
        $canViewWarranty = $aggregatePrivacy['warranty'];
        $canViewReportDetails = $reportPrivacy['customer_product_details'] && $canViewFinancial;
        $canExportInvoiceCsv = $reportPrivacy['invoice_csv'] && $canViewFinancial;

        /* ---------- Invoice KPI ---------- */

        $kpi = $canViewFinancial ? app(CrmReportingDataScope::class)->invoices(DB::table('invoices'))
            ->whereDate('invoice_date', '>=', $fromDate)
            ->whereDate('invoice_date', '<=', $toDate)
            ->selectRaw(
                "COUNT(*) AS total_invoices,
                 COALESCE(SUM(total_amount) FILTER (WHERE status IN ('Sent','Paid')), 0) AS invoiced,
                 COALESCE(SUM(total_amount) FILTER (WHERE status = 'Paid'), 0) AS paid,
                 COALESCE(SUM(total_amount) FILTER (WHERE status = 'Sent'), 0) AS outstanding,
                 COALESCE(SUM(total_amount) FILTER (WHERE status = 'Sent' AND due_date < ?), 0) AS overdue_amount,
                 COUNT(*) FILTER (WHERE status = 'Sent' AND due_date < ?) AS overdue_count",
                [$today, $today]
            )
            ->first() : null;

        $collectionRate = $canViewFinancial && (float) $kpi->invoiced > 0
            ? round(((float) $kpi->paid / (float) $kpi->invoiced) * 100, 1)
            : 0;

        /* ---------- Monthly chart ---------- */

        $monthly = $canViewFinancial ? $this->monthlySeries($from, $to) : [];

        /* ---------- Status breakdown ---------- */

        $statusBreakdown = $canViewFinancial ? app(CrmReportingDataScope::class)->invoices(DB::table('invoices'))
            ->whereDate('invoice_date', '>=', $fromDate)
            ->whereDate('invoice_date', '<=', $toDate)
            ->select('status', DB::raw('COUNT(*) AS total'), DB::raw('COALESCE(SUM(total_amount),0) AS amount'))
            ->groupBy('status')
            ->orderByDesc('amount')
            ->get() : collect();

        /* ---------- Top customers ---------- */

        $topCustomers = $canViewReportDetails ? app(CrmReportingDataScope::class)->invoices(DB::table('invoices as i'), 'i.sales_order_id')
            ->leftJoin('sales_orders as so', 'so.sales_order_id', '=', 'i.sales_order_id')
            ->leftJoin('quotations as q', 'q.quotation_id', '=', 'so.quotation_id')
            ->leftJoin('opportunities as o', 'o.opportunity_id', '=', 'q.opportunity_id')
            ->leftJoin('customers as c', 'c.customer_id', '=', 'o.customer_id')
            ->whereIn('i.status', self::ISSUED)
            ->whereDate('i.invoice_date', '>=', $fromDate)
            ->whereDate('i.invoice_date', '<=', $toDate)
            ->groupBy('c.customer_id', 'c.customer_name')
            ->select(
                DB::raw("COALESCE(c.customer_name, 'Unknown Customer') AS name"),
                DB::raw('COUNT(*) AS invoices'),
                DB::raw('SUM(i.total_amount) AS amount')
            )
            ->orderByDesc('amount')
            ->limit(5)
            ->get() : collect();

        /* ---------- Top products ---------- */

        $topProducts = $canViewReportDetails ? app(CrmReportingDataScope::class)->invoices(DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.invoice_id', '=', 'ii.invoice_id'), 'i.sales_order_id')
            ->leftJoin('products as p', 'p.product_id', '=', 'ii.product_id')
            ->whereIn('i.status', self::ISSUED)
            ->whereDate('i.invoice_date', '>=', $fromDate)
            ->whereDate('i.invoice_date', '<=', $toDate)
            ->groupBy('p.product_id', 'p.product_name', 'p.product_code')
            ->select(
                DB::raw("COALESCE(p.product_name, 'Unknown Product') AS name"),
                'p.product_code',
                DB::raw('SUM(ii.quantity) AS quantity'),
                DB::raw('SUM(ii.subtotal) AS amount')
            )
            ->orderByDesc('amount')
            ->limit(5)
            ->get() : collect();

        /* ---------- Service & Maintenance ---------- */

        $ticketBase = fn () => app(CrmReportingDataScope::class)->tickets(DB::table('service_tickets'))
            ->whereDate('opened_at', '>=', $fromDate)
            ->whereDate('opened_at', '<=', $toDate);

        $tickets = $canViewOperations ? [
            'total' => $ticketBase()->count(),
            'open' => $ticketBase()->whereNotIn('status', ['resolved', 'closed'])->count(),
            'avg_hours' => $ticketBase()
                ->whereNotNull('closed_at')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (closed_at - opened_at)) / 3600) AS hours')
                ->value('hours'),
            'by_status' => $ticketBase()
                ->select('status', DB::raw('COUNT(*) AS total'))
                ->groupBy('status')->orderByDesc('total')->get(),
            'by_priority' => $ticketBase()
                ->select('priority', DB::raw('COUNT(*) AS total'))
                ->groupBy('priority')->orderByDesc('total')->get(),
        ] : ['total' => 0, 'open' => 0, 'avg_hours' => null,
            'by_status' => collect(), 'by_priority' => collect()];

        $maintenance = $canViewOperations ? [
            'schedules' => app(CrmReportingDataScope::class)->schedules(DB::table('maintenance_schedules'))
                ->whereDate('scheduled_date', '>=', $fromDate)
                ->whereDate('scheduled_date', '<=', $toDate)
                ->count(),
            'records' => app(CrmReportingDataScope::class)->schedules(DB::table('maintenance_records'), 'maintenance_schedule_id')
                ->whereDate('performed_at', '>=', $fromDate)
                ->whereDate('performed_at', '<=', $toDate)
                ->count(),
            'overdue_now' => app(CrmReportingDataScope::class)->schedules(DB::table('maintenance_schedules'))
                ->whereIn('status', ['Scheduled', 'In Progress'])
                ->whereDate('scheduled_date', '<', $today)
                ->count(),
            'by_type' => app(CrmReportingDataScope::class)->schedules(DB::table('maintenance_schedules'))
                ->whereDate('scheduled_date', '>=', $fromDate)
                ->whereDate('scheduled_date', '<=', $toDate)
                ->select('maintenance_type', DB::raw('COUNT(*) AS total'))
                ->groupBy('maintenance_type')->orderByDesc('total')->get(),
            'by_result' => app(CrmReportingDataScope::class)->schedules(DB::table('maintenance_records'), 'maintenance_schedule_id')
                ->whereDate('performed_at', '>=', $fromDate)
                ->whereDate('performed_at', '<=', $toDate)
                ->select('result', DB::raw('COUNT(*) AS total'))
                ->groupBy('result')->orderByDesc('total')->get(),
        ] : ['schedules' => 0, 'records' => 0, 'overdue_now' => 0,
            'by_type' => collect(), 'by_result' => collect()];

        /* ---------- Pipeline & Warranty (snapshot saat ini) ---------- */

        $pipeline = $canViewPipeline ? app(CrmReportingDataScope::class)->pipeline(DB::table('opportunities'))
            ->select(
                'stage',
                DB::raw('COUNT(*) AS total'),
                DB::raw('COALESCE(SUM(estimated_value),0) AS value')
            )
            ->groupBy('stage')
            ->orderByDesc('value')
            ->get() : collect();

        $warranty = $canViewWarranty ? [
            'by_status' => app(CrmReportingDataScope::class)->warranty(DB::table('warranties'))
                ->select('status', DB::raw('COUNT(*) AS total'))
                ->groupBy('status')->orderByDesc('total')->get(),
            'expiring_soon' => app(CrmReportingDataScope::class)->warranty(DB::table('warranties'))
                ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', today()->addDays(30)->toDateString())
                ->count(),
        ] : ['by_status' => collect(), 'expiring_soon' => 0];

        return view('reports.index', [
            'from' => $from,
            'to' => $to,
            'presets' => $this->presets(),
            'kpi' => $kpi,
            'collectionRate' => $collectionRate,
            'monthly' => $monthly,
            'statusBreakdown' => $statusBreakdown,
            'topCustomers' => $topCustomers,
            'topProducts' => $topProducts,
            'canViewReportDetails' => $canViewReportDetails,
            'canExportInvoiceCsv' => $canExportInvoiceCsv,
            'canViewFinancial' => $canViewFinancial,
            'canViewOperations' => $canViewOperations,
            'canViewPipeline' => $canViewPipeline,
            'canViewWarranty' => $canViewWarranty,
            'tickets' => $tickets,
            'maintenance' => $maintenance,
            'pipeline' => $pipeline,
            'warranty' => $warranty,
        ]);
    }

    /**
     * Ekspor daftar invoice pada periode terpilih ke CSV.
     */
    public function exportInvoices(Request $request): StreamedResponse
    {
        $this->assertReportsView();

        // A7.6.5.4: optional least-privilege pilot. OFF by default.
        if ((bool) config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false)
            || (bool) config('crm_reporting.report_customer_product_privacy_pilot_enabled', false)
            || (bool) config('crm_reporting.report_aggregate_privacy_pilot_enabled', false)) {
            abort_unless(app(\App\Services\CrmInvoiceCsvPolicy::class)
                ->canExport(Auth::guard('web')->user()), 403);
        }

        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        [$from, $to] = $this->period($request);

        $rows = app(CrmReportingDataScope::class)->invoices(DB::table('invoices as i'), 'i.sales_order_id')
            ->leftJoin('sales_orders as so', 'so.sales_order_id', '=', 'i.sales_order_id')
            ->leftJoin('quotations as q', 'q.quotation_id', '=', 'so.quotation_id')
            ->leftJoin('opportunities as o', 'o.opportunity_id', '=', 'q.opportunity_id')
            ->leftJoin('customers as c', 'c.customer_id', '=', 'o.customer_id')
            ->whereDate('i.invoice_date', '>=', $from->toDateString())
            ->whereDate('i.invoice_date', '<=', $to->toDateString())
            ->orderBy('i.invoice_date')
            ->orderBy('i.invoice_number')
            ->select(
                'i.invoice_number',
                'i.invoice_date',
                'i.due_date',
                'c.customer_name',
                'so.sales_order_number',
                'i.status',
                'i.subtotal',
                'i.discount',
                'i.tax',
                'i.total_amount'
            );

        $filename = 'invoices_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Invoice Number', 'Invoice Date', 'Due Date', 'Customer',
                'Sales Order', 'Status', 'Subtotal', 'Discount', 'Tax', 'Total',
            ]);

            foreach ($rows->cursor() as $row) {
                $fields = [
                    $row->invoice_number,
                    $row->invoice_date,
                    $row->due_date,
                    $row->customer_name,
                    $row->sales_order_number,
                    $row->status,
                    $row->subtotal,
                    $row->discount,
                    $row->tax,
                    $row->total_amount,
                ];
                fputcsv($out, \App\Support\CrmCsvCellSanitizer::row($fields, [0, 3, 4, 5]));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Both web and CSV entrypoints must pass the same reporting permission check. */
    private function assertReportsView(): void
    {
        abort_unless(
            app(CrmNavigationAccess::class)->canView(Auth::guard('web')->user(), 'reports'),
            403
        );
    }
    /* =====================================================
       HELPERS
    ===================================================== */

    /**
     * Periode laporan; default: 1 Januari tahun ini sampai hari ini.
     */
    private function period(Request $request): array
    {
        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : today()->endOfDay();

        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : today()->startOfYear();

        return [$from, $to];
    }

    private function presets(): array
    {
        $fmt = fn (Carbon $date) => $date->toDateString();

        return [
            'This Month' => [
                'date_from' => $fmt(today()->startOfMonth()),
                'date_to' => $fmt(today()),
            ],
            'Last Month' => [
                'date_from' => $fmt(today()->subMonthNoOverflow()->startOfMonth()),
                'date_to' => $fmt(today()->subMonthNoOverflow()->endOfMonth()),
            ],
            'This Quarter' => [
                'date_from' => $fmt(today()->startOfQuarter()),
                'date_to' => $fmt(today()),
            ],
            'This Year' => [
                'date_from' => $fmt(today()->startOfYear()),
                'date_to' => $fmt(today()),
            ],
            'Last 12 Months' => [
                'date_from' => $fmt(today()->subMonthsNoOverflow(11)->startOfMonth()),
                'date_to' => $fmt(today()),
            ],
        ];
    }

    /**
     * Nilai invoiced & paid per bulan; bulan tanpa data diisi 0.
     * Dibatasi 24 bulan terakhir pada periode.
     */
    private function monthlySeries(Carbon $from, Carbon $to): array
    {
        $start = $from->copy()->startOfMonth();
        $end = $to->copy()->startOfMonth();

        if ($start->diffInMonths($end) > 23) {
            $start = $end->copy()->subMonths(23);
        }

        $data = app(CrmReportingDataScope::class)->invoices(DB::table('invoices'))
            ->whereDate('invoice_date', '>=', $start->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->selectRaw(
                "TO_CHAR(DATE_TRUNC('month', invoice_date), 'YYYY-MM') AS ym,
                 COALESCE(SUM(total_amount) FILTER (WHERE status IN ('Sent','Paid')), 0) AS invoiced,
                 COALESCE(SUM(total_amount) FILTER (WHERE status = 'Paid'), 0) AS paid"
            )
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        $series = [];

        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addMonth()) {
            $key = $cursor->format('Y-m');

            $series[] = [
                'label' => $cursor->format('M y'),
                'invoiced' => (float) ($data[$key]->invoiced ?? 0),
                'paid' => (float) ($data[$key]->paid ?? 0),
            ];
        }

        return $series;
    }
}