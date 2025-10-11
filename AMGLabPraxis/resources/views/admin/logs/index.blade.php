@extends('layouts.navbar')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
        <div>
            <h3 class="mb-0">Log attività</h3>
            <small class="text-muted">Ultime attività</small>
        </div>
        <div class="mt-2 mt-md-0">
            <a class="btn btn-secondary" href="{{ route('admin.pratiche.index') }}"><i class="fas fa-arrow-left me-1"></i> Torna indietro</a>
        </div>
    </div>
{{-- Table xs/md --}}
    <div class="d-lg-none">
        @forelse($logs as $log)
            @php
                // decodifica properties (se è JSON)
                $props = $log->properties;
                if (is_string($props) && $props !== '') {
                    $decoded = json_decode($props, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $props = $decoded;
                    } else {
                        $props = ['raw' => $props];
                    }
                }
                // id del collapse (unico)
                $cid = 'log-detail-' . $log->id;
            @endphp
            <article class="card mb-3 bg-light bg-gradient shadow-sm log-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <div class="text-muted small">{{ \Carbon\Carbon::parse($log->created_at)->setTimezone(config('app.timezone'))->format('d/m/Y') }}</div>
                            <div class="fw-semibold">{{ \Carbon\Carbon::parse($log->created_at)->setTimezone(config('app.timezone'))->format('H:i') }}</div>
                        </div>
                        <div class="flex-fill">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div style="width: 300px">
                                    <div class="small text-wrap text-muted">
                                        {{ optional($log->causer)->name ?? 'Sistema' }}
                                    </div>
                                    <div class="col-8 col-md-12 p-0">
                                        <div class="fw-semibold">{{ $log->description }}</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="d-flex gap-2">
                                        @if($log->subject)
                                            <form action="{{ route('admin.pratiche.edit', $log->subject_id) }}">
                                                <button href="#" class="icon-btn btn btn-sm btn-outline-info" title="Vai alla pratica">
                                                    <i class="fas fa-arrow-right"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button class="icon-btn btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#{{ $cid }}" aria-expanded="false" aria-controls="{{ $cid }}">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="collapse mt-3" id="{{ $cid }}">
                                <div class="small text-muted mb-2">Riferimento:
                                    @if($log->subject)
                                        <a href="{{ route('admin.pratiche.edit', $log->subject_id) }}">
                                            {{ optional($log->subject)->codice ?? ('Pratica #'.$log->subject_id) }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </div>

                                <div class="properties-render bg-light p-2 rounded">
                                    @include('admin.logs._render_properties', ['properties' => $props])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                    <div>Nessuna attività registrata</div>
                </div>
            </div>
        @endforelse
    </div>
{{-- Table lg --}}
    <div class="table-responsive">
        <table class="table table-sm table-secondary table-striped align-middle d-none d-lg-table">
            <thead class="table-dark">
                <tr>
                    <th>Data/Ora</th>
                    <th>Utente</th>
                    <th>Azione</th>
                    <th>Pratica</th>
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
                        <td style="max-width: 340px">
                            @include('admin.logs._render_properties', ['properties' => $log->properties])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pag-position">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection
