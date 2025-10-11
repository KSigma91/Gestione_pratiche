@if(!empty($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs))
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
        @foreach($breadcrumbs as $i => $item)
            @php
                $isLast = $i === array_key_last($breadcrumbs);
                $label = $item['label'] ?? '';
                $url = $item['url'] ?? null;
            @endphp

            @if($isLast)
                <li class="breadcrumb-item active text-muted" aria-current="page">{{ $label }}</li>
            @else
                @if(!empty($url))
                    <li class="breadcrumb-item"><a class="text-decoration-none text-secondary" href="{{ $url }}">{{ $label }}</a></li>
                @else
                    <li class="breadcrumb-item">{{ $label }}</li>
                @endif
            @endif
        @endforeach
        </ol>
    </nav>
@endif

<style>
.breadcrumb {
    background-color: transparent;
    padding-left: 0;
    margin-bottom: 1rem;
    list-style: none;
}
</style>
