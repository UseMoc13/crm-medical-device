{{-- A798: readonly operational assignment impact on User Edit --}}
@if(($assignmentImpact['total'] ?? 0) > 0)
<section class="card" aria-label="Existing operational assignments" style="margin-bottom:18px">
    <div class="card-head"><div>
        <h3>Existing Operational Assignments</h3>
        <p>Changing this user's role, branch, or status does not rewrite historical CRM records.</p>
    </div><span style="font-size:12px;color:#64748b">{{ number_format($assignmentImpact['total']) }} linked records</span></div>
    <div class="card-body">
        <div style="display:flex;flex-wrap:wrap;gap:8px">
            @foreach($assignmentImpact['items'] as $item)
                <span style="display:inline-flex;align-items:center;gap:6px;border:1px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:12px;color:#34415c;background:#f8fafc">
                    {{ $item['label'] }} <strong>{{ number_format($item['count']) }}</strong>
                    @if(!$item['eligible_now'])
                        <span style="color:#a16207" title="Current role/status is not eligible for new assignments">Review</span>
                    @endif
                </span>
            @endforeach
        </div>
        <p style="font-size:12px;color:#64748b;margin:12px 0 0;line-height:1.6">
            Existing assignments remain linked for traceability. If the employee becomes ineligible,
            reassign open operational work manually. This panel never changes permissions or past records.
        </p>
    </div>
</section>
@endif
