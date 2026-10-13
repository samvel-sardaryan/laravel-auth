@if ($paginator->hasPages())
    <nav class="pagination">
        @unless ($paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}">Previous</a>
        @endunless

        @foreach ($elements as $element)
            @if (is_string($element))
                <span>{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <strong>{{ $page }}</strong>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Next</a>
        @endif
    </nav>
@endif
