@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="pagination">
        <div class="pagination-meta">
            <span class="pagination-summary">
                Pagina {{ $paginator->currentPage() }}
            </span>
        </div>

        <ul class="pagination-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Anterior">
                    <span class="page-link page-link-nav" aria-hidden="true">&lsaquo; Anterior</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link page-link-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        &lsaquo; Anterior
                    </a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link page-link-nav" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        Siguiente &rsaquo;
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Siguiente">
                    <span class="page-link page-link-nav" aria-hidden="true">Siguiente &rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif

