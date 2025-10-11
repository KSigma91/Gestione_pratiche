@extends('layouts.navbar')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
        <div>
            <h3 class="mb-0">Sessioni attive</h3>
            <p class="text-muted m-0">Vedi qui i dispositivi / sessioni in cui sei loggato. Puoi terminare le sessioni remote.</p>
        </div>
        <div class="mt-2 mt-lg-0">
            <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}"><i class="fas fa-arrow-left me-1"></i> Torna indietro</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="list-group">
        @forelse($sessions as $s)
            <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start">
                <div class="me-3 w-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $s->user_agent }}</div>
                            <div class="small text-muted">
                                {{ $s->user_agent_raw ? \Illuminate\Support\Str::limit($s->user_agent_raw, 120) : '-' }}
                            </div>
                        </div>

                        <div class="text-end ms-3">
                            <div class="small text-muted">IP: <strong>{{ $s->ip_address }}</strong></div>
                            <div class="small text-muted">
                                @if($s->last_activity)
                                    Ultima attività: {{ $s->last_activity->format('d/m/Y H:i') }}
                                @else
                                    Ultima attività: -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        @if($s->is_current)
                            <span class="badge bg-primary">Sessione corrente</span>
                        @else
                            <span class="badge bg-secondary">Sessione remota</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 mt-md-0 ms-md-3 text-end">
                    @if(!$s->is_current)
                        <form method="POST" action="{{ route('admin.sessions.destroy', $s->id) }}" onsubmit="return confirm('Termino questa sessione?');">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Termina</button>
                        </form>
                    @else
                        <button class="btn btn-sm btn-outline-secondary" disabled>Non terminabile</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="list-group-item text-muted">Nessuna sessione trovata.</div>
        @endforelse
    </div>

    <div class="mt-3">
{{-- fine elenco sessioni --}}
        <form id="terminate-others-form" method="POST" action="{{ route('admin.sessions.destroy.others') }}">
            @csrf
            @php
                $remoteCount = $sessions->where('is_current', false)->count();
            @endphp
            <button id="terminate-others-btn" type="button" class="btn btn-danger btn-sm" data-remote-count="{{ $remoteCount }}">
                Termina tutte le altre sessioni
            </button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('terminate-others-btn');
    var form = document.getElementById('terminate-others-form');

    if (!btn || !form) return;

    btn.addEventListener('click', function (e) {
        e.preventDefault();

        var remoteCount = parseInt(btn.getAttribute('data-remote-count') || '0', 10);

        // Se Tingle è disponibile, usalo
        if (typeof tingle !== 'undefined') {

            var modal = new tingle.modal({
                footer: true,
                stickyFooter: false,
                closeMethods: ['overlay', 'escape'],
                closeLabel: "Chiudi",
                cssClass: ['tingle-modal--small'],
                onOpen: function() {},
                onClose: function() {
                    modal.destroy();
                }
            });

            var title = '<h5 class="mb-2">Termina tutte le altre sessioni?</h5>';
            var bodyMsg = '';

            if (remoteCount > 0) {
                bodyMsg = '<p>Se procedi terminerai <strong>' + remoteCount + '</strong> session' + (remoteCount > 1 ? 'i' : 'e') + ' remote. Chiudendo queste sessioni, gli utenti (o i tuoi altri dispositivi) verranno disconnessi alla prossima richiesta.</p>';
            } else {
                bodyMsg = '<p>Non sono state rilevate altre sessioni attive. Vuoi comunque procedere?</p>';
            }

            bodyMsg += '<p class="text-muted small mb-0">Questa operazione non può essere annullata: dovrai fare nuovamente il login se desiderato.</p>';

            modal.setContent(title + bodyMsg);

            // Aggiungi pulsante conferma
            var confirmBtn = modal.addFooterBtn('Conferma e termina', 'btn btn-primary', function() {
                // disabilita il bottone per evitare doppio submit
                confirmBtn.setAttribute('disabled', 'disabled');
                // invia la form in modo sicuro
                form.submit();
            });

            // Aggiungi pulsante annulla
            modal.addFooterBtn('Annulla', 'btn btn-secondary ms-3', function() {
                modal.close();
            });

            modal.open();

        } else {
            // fallback: prompt nativo
            var ok = confirm('Termino tutte le altre sessioni?');
            if (ok) form.submit();
        }
    });
});
</script>
@endpush

