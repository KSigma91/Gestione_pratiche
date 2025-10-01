@php
    /**
     * Accetta $properties (stringa o array). Restituisce HTML sicuro con testo leggibile.
     * Uso: @include('admin.logs._render_properties', ['properties' => $log->properties])
     */
    $props = $properties ?? null;

    // Normalizza: se stringa, prova a decodificare json
    if (is_string($props) && $props !== '') {
        $decoded = json_decode($props, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $props = $decoded;
        } else {
            $props = ['raw' => $props];
        }
    }

    // Se è oggetto modello Activity che espone properties, converti
    if (is_object($props) && property_exists($props, 'toArray')) {
        $props = (array) $props;
    }
@endphp

@if(empty($props))
    <small class="text-muted">Nessun dettaglio</small>
@else
    @if(!empty($props['message']))
        <div class="small">{!! nl2br(e($props['message'])) !!}</div>
    @elseif(!empty($props['old']) && !empty($props['attributes']))
        <div class="small">
            @foreach($props['old'] as $field => $old)
                @php $new = data_get($props['attributes'], $field); @endphp
                @if($old != $new)
                    <div>Campo <strong>{{ $field }}</strong>: «{{ $old }}» → «{{ $new }}»</div>
                @endif
            @endforeach
        </div>
    @elseif(!empty($props['raw']) && is_string($props['raw']))
        <div class="small">{!! nl2br(e($props['raw'])) !!}</div>
    @else
        {{-- fallback: mostra key: value in righe semplici --}}
        <div class="small">
            @foreach($props as $k => $v)
                @if($k === 'message') @continue @endif
                <div><strong>{{ $k }}</strong>: {{ is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v }}</div>
            @endforeach
        </div>
    @endif
@endif
