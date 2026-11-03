{{--
    Daftar batang horizontal.
    Variabel: $rows = [ ['label'=>, 'sub'=>?, 'value'=>(angka), 'display'=>(teks), 'color'=>?], ... ]
              $emptyText (opsional)
--}}
@php
    $rows = collect($rows);
    $max = max(1, (float) $rows->max('value'));
@endphp

@if($rows->isEmpty())
    <div class="rp-empty">{{ $emptyText ?? 'No data for this period.' }}</div>
@else
    <div class="rp-list">
        @foreach($rows as $row)
            <div>
                <div class="rp-row-head">
                    <div class="rp-row-label">
                        {{ $row['label'] }}
                        @if(!empty($row['sub']))
                            <span class="rp-row-sub">{{ $row['sub'] }}</span>
                        @endif
                    </div>
                    <div class="rp-row-value">{{ $row['display'] }}</div>
                </div>

                <div class="rp-track">
                    <div
                        class="rp-fill {{ $row['color'] ?? '' }}"
                        style="width: {{ round(((float) $row['value'] / $max) * 100, 1) }}%"
                    ></div>
                </div>
            </div>
        @endforeach
    </div>
@endif