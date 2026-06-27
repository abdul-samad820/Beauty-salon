@if ($paginator->hasPages())
<nav class="d-flex justify-content-between align-items-center" aria-label="Pagination">

    {{-- Results Info --}}
    <div class="d-none d-sm-block">
        <p class="pagination-info" style="margin: 0; font-size: 0.75rem; color: var(--text-3); letter-spacing: 0.05em;">
            {!! __('Showing') !!} <span class="fw-bold" style="color: var(--text);">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!} <span class="fw-bold" style="color: var(--text);">{{ $paginator->lastItem() }}</span>
            {!! __('of') !!} <span class="fw-bold" style="color: var(--text);">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>
    </div>

    {{-- Pagination Links --}}
    <ul class="pagination lux-pagination mb-0" style="gap: 0.4rem;">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
        <li class="page-item disabled" aria-disabled="true">
            <span class="page-link" style="border-radius: 4px;"><i class="bi bi-chevron-left"></i></span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="border-radius: 4px;"><i class="bi bi-chevron-left"></i></a>
        </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
        @if (is_string($element))
        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
        @endif

        @if (is_array($element))
        @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
        <li class="page-item active" aria-current="page">
            <span class="page-link" style="border-radius: 4px; border-color: var(--gold); background: var(--gold); color: var(--charcoal);">{{ $page }}</span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $url }}" style="border-radius: 4px;">{{ $page }}</a>
        </li>
        @endif
        @endforeach
        @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="border-radius: 4px;"><i class="bi bi-chevron-right"></i></a>
        </li>
        @else
        <li class="page-item disabled">
            <span class="page-link" style="border-radius: 4px;"><i class="bi bi-chevron-right"></i></span>
        </li>
        @endif
    </ul>
</nav>
@endif
