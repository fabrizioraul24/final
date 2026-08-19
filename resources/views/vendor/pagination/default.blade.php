@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="pagination">
        <div class="pagination-meta">
            <span class="pagination-summary">
                Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
            </span>
            <span class="pagination-summary pagination-summary--page">
                Pagina {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
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
                    <a class="page-link page-link-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">
                        &lsaquo; Anterior
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link page-link-dots">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link page-link-nav" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">
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

