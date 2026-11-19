<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Dashboard KPI
        |--------------------------------------------------------------------------
        */

        $totalCustomers = $this->countTable('customers');

        $activeLeads = $this->countActiveLeads();

        $opportunities = $this->countTable('opportunities');

        $serviceTickets = $this->countTable('service_tickets');


        /*
        |--------------------------------------------------------------------------
        | Recent Activities
        |--------------------------------------------------------------------------
        */

        $recentActivities = collect();

        if (Schema::hasTable('activities')) {

            $recentActivities = DB::table('activities')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Dashboard Data
        |--------------------------------------------------------------------------
        */

        $stats = [
            'customers' => $totalCustomers,
            'leads' => $activeLeads,
            'opportunities' => $opportunities,
            'service_tickets' => $serviceTickets,
        ];


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('dashboard.index', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Count Table
    |--------------------------------------------------------------------------
    */

    private function countTable(string $table): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Count Active Leads
    |--------------------------------------------------------------------------
    */

    private function countActiveLeads(): int
    {
        if (!Schema::hasTable('leads')) {
            return 0;
        }

        /*
         * Jika tabel leads mempunyai kolom status,
         * kita hanya menghitung lead yang masih aktif.
         */

        if (Schema::hasColumn('leads', 'status')) {

            return DB::table('leads')
                ->whereNotIn('status', [
                    'closed',
                    'converted',
                    'lost',
                ])
                ->count();
        }

        /*
         * Jika kolom status belum tersedia,
         * gunakan total leads sementara.
         */

        return DB::table('leads')->count();
    }
}