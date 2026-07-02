@if($paginator->hasPages())
<div style="padding:.8rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
    <p style="font-size:.72rem;color:var(--text-3);margin:0;">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </p>
    <nav aria-label="Pagination">
        <div style="display:flex;gap:.3rem;">
            @if(!$paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:transparent;color:var(--text-2);display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.78rem;" aria-label="Previous page">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </a>
            @endif

            @foreach($paginator->getUrlRange(max(1,$paginator->currentPage()-1), min($paginator->lastPage(),$paginator->currentPage()+1)) as $pg => $url)
            <a href="{{ $url }}" aria-label="Page {{ $pg }}" aria-current="{{ $pg === $paginator->currentPage() ? 'page' : 'false' }}" style="width:30px;height:30px;border-radius:6px;border:1px solid {{ $pg == $paginator->currentPage() ? 'var(--gold)' : 'var(--border)' }};background:{{ $pg == $paginator->currentPage() ? 'var(--gold)' : 'transparent' }};color:{{ $pg == $paginator->currentPage() ? '#1a1400' : 'var(--text-2)' }};display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.78rem;">
                {{ $pg }}
            </a>
            @endforeach

            @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:transparent;color:var(--text-2);display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.78rem;" aria-label="Next page">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </a>
            @endif
        </div>
    </nav>
</div>
@endif
