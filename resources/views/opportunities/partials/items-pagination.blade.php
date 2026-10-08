@if($items->total() > 0)

<div class="modal-pagination">

    <div class="modal-pagination-info">

        Showing

        <strong>
            {{ $items->firstItem() }}
        </strong>

        to

        <strong>
            {{ $items->lastItem() }}
        </strong>

        of

        <strong>
            {{ $items->total() }}
        </strong>

        items

    </div>


    <div class="modal-pagination-links">

        {{-- PREVIOUS --}}

        @if($items->onFirstPage())

            <button
                type="button"
                class="modal-page disabled"
                disabled>

                ←

            </button>

        @else

            <button
                type="button"
                class="modal-page"
                data-items-page="{{ $items->currentPage() - 1 }}">

                ←

            </button>

        @endif


        {{-- PAGE NUMBERS --}}

        @php

            $startPage = max(
                1,
                $items->currentPage() - 2
            );

            $endPage = min(
                $items->lastPage(),
                $items->currentPage() + 2
            );

        @endphp


        @for(
            $page = $startPage;
            $page <= $endPage;
            $page++
        )

            <button
                type="button"
                class="modal-page {{ $page === $items->currentPage() ? 'active' : '' }}"
                data-items-page="{{ $page }}">

                {{ $page }}

            </button>

        @endfor


        {{-- NEXT --}}

        @if($items->hasMorePages())

            <button
                type="button"
                class="modal-page"
                data-items-page="{{ $items->currentPage() + 1 }}">

                →

            </button>

        @else

            <button
                type="button"
                class="modal-page disabled"
                disabled>

                →

            </button>

        @endif

    </div>

</div>

@endif