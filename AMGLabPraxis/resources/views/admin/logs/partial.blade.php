<div class="p-3">
    <h5>Ultime attività</h5>

    <div class="list-group list-group-flush">
        @foreach($logs as $log)
            @php
                $props = $log->properties;
                if (is_string($props) && $props !== '') {
                    $decoded = json_decode($props, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $props = $decoded;
                    } else {
                        $props = ['raw' => $props];
                    }
                }
            @endphp

            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <div>
                        <strong>{{ $log->description }}</strong>
                        <div class="small text-muted">
                            {{ \Carbon\Carbon::parse($log->created_at)->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}
                            @if($log->causer)
                              — {{ $log->causer->name }}
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        @if($log->subject)
                            <a href="{{ route('admin.pratiche.edit', $log->subject_id) }}" class="btn btn-sm btn-outline-info">Vai alla pratica</a>
                        @endif
                    </div>
                </div>

                {{-- <div class="mt-2">
                    @include('admin.logs._render_properties', ['properties' => $log->properties])
                </div> --}}
            </div>
        @endforeach
    </div>
</div>
