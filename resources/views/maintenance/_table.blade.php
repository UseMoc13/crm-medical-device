{{--
    Tabel record (dipakai di kartu detail dan di modal Show All).
    Variabel: $rows, $maintenance, $tbodyId (opsional)
--}}
<table class="mt-rec-table">

    <thead>
        <tr>
            <th>Performed At</th>
            <th>Performed By</th>
            <th>Result</th>
            <th>Condition Before</th>
            <th>Action Taken</th>
            <th>Condition After</th>
            <th>Notes</th>
            <th class="mt-col-actions">Actions</th>
        </tr>
    </thead>

    <tbody @isset($tbodyId) id="{{ $tbodyId }}" @endisset>

        @foreach($rows as $row)
            <tr data-result="{{ strtolower($row->result ?? '') }}">

                <td>
                    <strong>{{ $row->performed_at?->format('d M Y') ?? '-' }}</strong>
                    <div class="mt-sub">{{ $row->performed_at?->format('H:i') }}</div>
                </td>

                <td>{{ $row->user?->name ?? 'Unknown' }}</td>

                <td>
                    <span class="mt-badge mt-result-{{ \Illuminate\Support\Str::slug($row->result) }}">
                        {{ $row->result ?? '-' }}
                    </span>
                </td>

                <td><span class="mt-clip">{{ filled($row->condition_before) ? $row->condition_before : '-' }}</span></td>
                <td><span class="mt-clip">{{ filled($row->action_taken) ? $row->action_taken : '-' }}</span></td>
                <td><span class="mt-clip">{{ filled($row->condition_after) ? $row->condition_after : '-' }}</span></td>
                <td><span class="mt-clip">{{ filled($row->notes) ? $row->notes : '-' }}</span></td>

                <td class="mt-col-actions">
                    <div class="mt-actions">

                        <a
                            href="{{ route('maintenance.records.edit', [$maintenance->maintenance_schedule_id, $row->maintenance_record_id]) }}"
                            class="mt-icon-btn edit"
                            title="Edit Maintenance Record"
                            aria-label="Edit maintenance record"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                            </svg>
                        </a>

                        <form
                            method="POST"
                            action="{{ route('maintenance.records.destroy', [$maintenance->maintenance_schedule_id, $row->maintenance_record_id]) }}"
                            onsubmit="return confirm('Delete this maintenance record?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="mt-icon-btn delete"
                                title="Delete Maintenance Record"
                                aria-label="Delete maintenance record"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2"/>
                                    <path d="m19 6-1 14H6L5 6"/>
                                    <path d="M10 11v5M14 11v5"/>
                                </svg>
                            </button>
                        </form>

                    </div>
                </td>

            </tr>
        @endforeach

    </tbody>

</table>
