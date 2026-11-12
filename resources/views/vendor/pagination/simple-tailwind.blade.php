{{-- Global simple pagination view for CRM Medical Device. --}}
@if ($paginator->hasPages())
    <nav class="crm-pagination" role="navigation" aria-label="Pagination Navigation">
        @if ($paginator->onFirstPage())
            <span class="crm-page-button crm-page-disabled" aria-disabled="true"><span aria-hidden="true">&larr;</span> Previous</span>
        @else
            <a class="crm-page-button crm-page-step" href="{{ $paginator->previousPageUrl() }}" rel="prev"><span aria-hidden="true">&larr;</span> Previous</a>
        @endif
        @if ($paginator->hasMorePages())
            <a class="crm-page-button crm-page-step" href="{{ $paginator->nextPageUrl() }}" rel="next">Next <span aria-hidden="true">&rarr;</span></a>
        @else
            <span class="crm-page-button crm-page-disabled" aria-disabled="true">Next <span aria-hidden="true">&rarr;</span></span>
        @endif
    </nav>
@endif
