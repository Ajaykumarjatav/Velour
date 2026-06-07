@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex w-full max-w-full items-center justify-between gap-3 sm:mr-[4.75rem]">
        @if ($paginator->onFirstPage())
            <span class="pagination-link disabled cursor-not-allowed opacity-50">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="pagination-link disabled cursor-not-allowed opacity-50">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
