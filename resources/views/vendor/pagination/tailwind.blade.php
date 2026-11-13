{{-- Shared pagination CSS is loaded here so page styles survive layout updates. --}}
@once
    @include('components._pagination_style')
@endonce
{{-- Global pagination view for CRM Medical Device. Laravel default tailwind paginator override. --}}
@if ($paginator->hasPages())
    <nav class="crm-pagination" role="navigation" aria-label="Pagination Navigation">
        @if ($paginator->onFirstPage())
            <span class="crm-page-button crm-page-disabled" aria-disabled="true" aria-label="Previous page">
                <span aria-hidden="true">&larr;</span> Previous
            </span>
        @else
            <a class="crm-page-button crm-page-step" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">
                <span aria-hidden="true">&larr;</span> Previous
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="crm-page-ellipsis" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="crm-page-button crm-page-current" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                    @else
                        <a class="crm-page-button crm-page-number" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="crm-page-button crm-page-step" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">
                Next <span aria-hidden="true">&rarr;</span>
            </a>
        @else
            <span class="crm-page-button crm-page-disabled" aria-disabled="true" aria-label="Next page">
                Next <span aria-hidden="true">&rarr;</span>
            </span>
        @endif
    </nav>
@endif
