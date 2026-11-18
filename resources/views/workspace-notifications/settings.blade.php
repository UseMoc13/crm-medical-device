@extends('layouts.app')
@section('title', 'Notification Settings')
@section('content')
<div class="page-head">
    <div><h1>Notification Settings</h1><p>Choose the workspace updates you want to receive and when reminders should arrive.</p></div>
    <div class="actions"><a href="{{ route('workspace-notifications.index') }}" class="btn">&larr; Back to Notifications</a></div>
</div>
<form action="{{ route('workspace-notifications.settings.update') }}" method="POST" class="wsn-settings-layout">
    @csrf @method('PATCH')
    <section class="card wsn-settings-card">
        <div class="card-head"><div><h3>Workspace updates</h3><p>Choose which work events generate a notification for your account.</p></div></div>
        <div class="card-body">
            @foreach([
                'assignment_enabled' => ['New TO-DO assignments', 'A new work task has been assigned to you.'],
                'collaboration_enabled' => ['Collaboration invitations', 'You have been invited to an Activity or Work TO-DO.'],
                'submission_enabled' => ['Submissions to review', 'An assignee has completed work that awaits your review.'],
                'review_enabled' => ['Approval and revision decisions', 'A creator has approved or requested revisions to your submission.'],
                'crm_status_enabled' => ['CRM status updates', 'Opt in to status changes for CRM records you can still access.'],
            ] as $field => [$heading, $description])
                <label class="wsn-settings-choice">
                    <span><strong>{{ $heading }}</strong><small>{{ $description }}</small></span>
                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings[$field]))>
                </label>
            @endforeach
        </div>
    </section>
    <section class="card wsn-settings-card">
        <div class="card-head"><div><h3>Reminders</h3><p>Choose which schedules may notify you.</p></div></div>
        <div class="card-body">
            @foreach([
                'reminder_note_enabled' => ['Personal Notes', 'Uses the reminder time already saved on the Note.'],
                'reminder_calendar_enabled' => ['Personal Calendar', 'Personal event start times.'],
                'reminder_activity_enabled' => ['Activities', 'Activities you own or explicitly collaborate on.'],
                'reminder_todo_enabled' => ['TO-DO deadlines', 'Tasks you own, are assigned to, or explicitly collaborate on.'],
            ] as $field => [$heading, $description])
                <label class="wsn-settings-choice">
                    <span><strong>{{ $heading }}</strong><small>{{ $description }}</small></span>
                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings[$field]))>
                </label>
            @endforeach
            <div class="wsn-settings-lead">
                <label for="wsnReminderLead">Remind me before Calendar, Activity and TO-DO schedules</label>
                <select id="wsnReminderLead" name="reminder_lead_minutes" required>
                    @foreach([0 => 'At the scheduled time',15 => '15 minutes before',60 => '1 hour before',180 => '3 hours before',1440 => '1 day before'] as $minutes => $label)
                        <option value="{{ $minutes }}" @selected((int) old('reminder_lead_minutes', $settings['reminder_lead_minutes']) === $minutes)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('reminder_lead_minutes')<small class="wsn-settings-error">{{ $message }}</small>@enderror
                <small>Personal Notes keep their own reminder time. Changes affect newly generated notifications only; existing inbox items remain.</small>
            </div>
        </div>
    </section>
    <div class="wsn-settings-actions"><button type="submit" class="btn primary">Save Preferences</button></div>
</form>
@endsection
