<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkspaceAuditEvent;
use App\Services\WorkspaceAuditAccess;
use App\Services\WorkspaceAuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class WorkspaceAuditController extends Controller
{
    public function index(Request $request, WorkspaceAuditAccess $access): View
    {
        $access->assertView($request->user('web'));
        abort_unless(Schema::hasTable('workspace_audit_events'), 503, 'Workspace audit migration is required.');
        $queryParams = $request->validate([
            'event_type' => ['nullable', Rule::in(WorkspaceAuditTrail::EVENTS)],
            'subject_type' => ['nullable', Rule::in(WorkspaceAuditTrail::SUBJECTS)],
            'actor_user_id' => ['nullable', 'uuid'],
            'subject_id' => ['nullable', 'uuid'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $query = WorkspaceAuditEvent::query()->with(['actor:user_id,name']);
        if (!empty($queryParams['event_type'])) $query->where('event_type', $queryParams['event_type']);
        if (!empty($queryParams['subject_type'])) $query->where('subject_type', $queryParams['subject_type']);
        if (!empty($queryParams['actor_user_id'])) $query->where('actor_user_id', $queryParams['actor_user_id']);
        if (!empty($queryParams['subject_id'])) $query->where('subject_id', $queryParams['subject_id']);
        if (!empty($queryParams['from'])) $query->whereDate('created_at', '>=', $queryParams['from']);
        if (!empty($queryParams['to'])) $query->whereDate('created_at', '<=', $queryParams['to']);

        $events = $query->orderByDesc('created_at')->orderByDesc('audit_event_id')
            ->paginate(25)->withQueryString();
        return view('workspace-audit.index', [
            'events' => $events,
            'filters' => $queryParams,
            'eventTypes' => WorkspaceAuditTrail::EVENTS,
            'subjectTypes' => WorkspaceAuditTrail::SUBJECTS,
        ]);
    }
}
