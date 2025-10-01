@extends('layouts.navbar')

@section('content')
<div class="container py-4">
    <h3>Log attività</h3>

    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Data/Ora</th>
                    <th>Utente</th>
                    <th>Azione</th>
                    <th>Riferimento Pratica</th>
                    <th>Dettagli</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    @php
                        // properties possono essere testo o JSON salvato in DB; proviamo a decodificare
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

                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($log->causer)->name ?? 'Sistema' }}</td>
                        <td>{{ $log->description }}</td>
                        <td>
                            @if($log->subject)
                                <a href="{{ route('admin.pratiche.edit', $log->subject_id) }}">
                                    {{ optional($log->subject)->codice ?? 'Pratica #'.$log->subject_id }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        {{-- <td>
                            @include('admin.logs._render_properties', ['properties' => $log->properties])
                        </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
