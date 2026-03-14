@if ($paginator->hasPages())
    <div class="d-flex flex-wrap justify-content-end">
        <ul class="pagination justify-content-center justify-content-md-start">
            {{-- <li class="page-item"><a class="page-link" href="#">Назад</a></li> --}}
            @if (!$paginator->onFirstPage())
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><em class="icon ni ni-chevrons-left"></em></a></li>
            @endif

            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item"><span class="page-link"><em class="icon ni ni-more-h"></em></span></li>
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


            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><em class="icon ni ni-chevrons-right"></em></a></li>
            @endif
        </ul><!-- .pagination -->
    </div>


@endif
