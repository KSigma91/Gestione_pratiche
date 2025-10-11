@extends('layouts.navbar')

@section('content')
<div class="py-4 px-0">
    <div class="d-flex flex-wrap justify-content-end justify-content-lg-between align-items-center gap-2 mb-3">
        <h3 class="mb-0 me-auto me-lg-0">Lista Pratiche</h3>
        <a class="btn btn-outline-success" href="{{ route('admin.pratiche.create') }}" title="Aggiungiere pratica" aria-label="Aggiungiere pratica"><i class="fas fa-plus me-1"></i> Aggiungi pratica</a>
        <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}"><i class="fas fa-arrow-left me-1"></i> Torna alla dashboard</a>
    </div>
{{-- Ricerca filtrata --}}
    <form method="GET" class="form-inline">
        <div class="row g-2 mb-3">
<!-- Ricerca per cliente -->
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input name="cliente" value="{{ request('cliente') }}" type="search" class="form-control" placeholder="Cerca per cliente, codice, caso o anno (es. Rossi 2023)">
                </div>
            </div>
<!-- Ricerca per stato pratica -->
            <div class="col-12 col-md-4">
                <select name="stato" class="form-select">
                    <option value="tutti" {{ request('stato') == 'tutti' ? 'selected' : '' }}>Tutti gli stati</option>
                    <option value="in_giacenza" {{ request('stato') == 'in_giacenza' ? 'selected':'' }}>In giacenza</option>
                    <option value="in_lavorazione" {{ request('stato') == 'in_lavorazione' ? 'selected':'' }}>In lavorazione</option>
                    <option value="completata" {{ request('stato') == 'completata' ? 'selected':'' }}>Completata</option>
                    <option value="annullata" {{ request('stato') == 'annullata' ? 'selected':'' }}>Annullata</option>
                </select>
            </div>
<!-- Ordinamento per pratica numerica -->
            <div class="col-12 col-md-4">
                <select name="ordinamento" id="ordinamento" class="form-select" aria-label="multiple select example">
                    <option value="" class="text-secondary">Ordinamento per pratica</option>
                    <option value="asc" {{ request('ordinamento') === 'asc' ? 'selected' : '' }}>Ordinamento per pratica: Crescente</option>
                    <option value="desc" {{ request('ordinamento') === 'desc' ? 'selected' : '' }}>Ordinamento per pratica: Decrescente</option>
                </select>
            </div>
<!-- Ordinamento per data -->
            <div class="col-12 col-md-4">
                <select name="ordinamento_data" id="ordinamento_data" class="form-select">
                    <option value="" class="text-secondary">Ordinamento per data</option>
                    <option value="asc" {{ request('ordinamento_data') === 'asc' ? 'selected' : '' }}>Ordinamento per data: Crescente</option>
                    <option value="desc" {{ request('ordinamento_data') === 'desc' ? 'selected' : '' }}>Ordinamento per data: Decrescente</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary">Filtra</button>
                <a href="{{ route('admin.pratiche.index') }}" class="btn btn-outline-secondary ms-1">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </div>
    </form>
{{-- Alert del filtraggio giacenze --}}
    @if(request('stato') == 'in_giacenza')
        <div class="alert alert-warning small">
            Filtrate: <strong>solo pratiche in giacenza</strong>.
            <a href="{{ route('admin.pratiche.index') }}" class="ms-2">Mostra tutte</a>
        </div>
    @endif
{{-- Alert se non è presente nessuna pratica o non combacia con le ricerche/filtro --}}
    @if($pratiche->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                <p class="mb-0 text-muted">Nessuna pratica trovata.</p>
            </div>
        </div>
    @else
{{-- GRID per mobile (xs-md) --}}
        <div class="d-lg-none mb-3">
            <div class="row g-3">
                @foreach($pratiche as $p)
                    <div class="col-12">
                        <div class="card shadow-sm practice-card h-100">
                            <div class="card-body d-flex">
                                <div class="me-3">
                                    <div class="avatar bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                        <i class="fas fa-file-alt fa-lg text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start">
                                        <div style="min-width: 250px">
{{-- codice e nome cliente --}}
                                            <h6 class="mb-1">{{ $p->codice }}</h6>
                                            <div class="small text-muted">{{ $p->cliente_nome }} — <em>{{ $p->tipo_pratica }}</em></div>
                                        </div>
                                        <div class="text-start text-md-end">
                                            <div class="mb-1">
{{-- stato pratica --}}
                                                @include('partials._state_badge', ['stato' => $p->stato])
                                            </div>
                                            <small class="text-muted d-block">{{ $p->data_arrivo ? $p->data_arrivo->format('d/m/Y H:i') : '-' }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
{{-- stato fattura --}}
                                        @if($p->stato_fattura === 'emessa')
                                            <span class="badge bg-success">Fattura: Emessa</span>
                                        @else
                                            <span class="badge bg-secondary">Fattura: Non emessa</span>
                                        @endif

{{-- stato pagamento --}}
                                        @if($p->stato_pagamento === 'pagato')
                                            <span class="badge bg-success">Pagamento: Pagato</span>
                                        @else
                                            <span class="badge bg-secondary">Pagamento: Non pagato</span>
                                        @endif
                                    </div>

                                    <div class="col-12 px-0">
<!-- Note -->
                                        <div class="my-3">
                                            {{ Str::limit($p->note ?? '-', 120) }}
                                        </div>

                                        <div class="d-flex justify-content-start justify-content-md-end gap-2" role="group" aria-label="Azioni">
<!-- Visualizza -->
                                            <form action="{{ route('admin.pratiche.show', $p->id) }}">
                                                <button class="btn btn-outline-info btn-sm icon-btn" href="#" title="Visualizza">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </form>
<!-- Modifica -->
                                            <form action="{{ route('admin.pratiche.edit', $p->id) }}">
                                                <button class="btn btn-sm icon-btn" href="#" title="Modifica pratica" aria-label="Modifica pratica" style="background-color: #3B71CA">
                                                    <i class="fas fa-edit text-white"></i>
                                                </button>
                                            </form>
<!-- Sposta in giacenza -->
                                            <form method="POST" action="{{ route('admin.pratiche.giacenza', $p->id) }}" class="d-inline">
                                                @csrf
                                                <button type="button"
                                                        class="btn btn-sm btn-secondary btn-giacenza icon-btn"
                                                        data-id="{{ $p->id }}"
                                                        data-codice="{{ $p->codice }}"
                                                        data-cliente="{{ $p->cliente_nome }}"
                                                        data-stato="{{ $p->stato }}"
                                                        data-remove-url="{{ route('admin.pratiche.remove_giacenza', $p->id) }}"
                                                        data-remove-method="POST"
                                                        title="Metti in giacenza">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                            </form>
<!-- sposta nel cestino -->
                                            <form method="POST" action="{{ route('admin.pratiche.delete', $p->id) }}" class="d-inline">
                                                @csrf
                                                {{-- se usi method spoofing per DELETE --}}
                                                {{-- @method('DELETE') --}}
                                                <button type="button"
                                                        class="btn btn-sm btn-dark btn-cestino icon-btn"
                                                        data-id="{{ $p->id }}"
                                                        data-codice="{{ $p->codice }}"
                                                        data-cliente="{{ $p->cliente_nome }}"
                                                        title="Sposta nel cestino">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
<!-- Eliminazione definitiva -->
                                            <form method="POST" action="{{ route('admin.pratiche.force-delete', $p->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                        class="btn btn-sm btn-danger btn-delete-permanent icon-btn"
                                                        data-id="{{ $p->id }}"
                                                        data-codice="{{ $p->codice }}"
                                                        data-cliente="{{ $p->cliente_nome }}"
                                                        title="Elimina definitivamente">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
<!-- Table lg -->
        <table class="table table-light table-striped align-middle d-none d-lg-table">
            <thead class="table-dark">
                <tr>
                    <th>Codice</th>
                    <th>Cliente</th>
                    <th>Caso</th>
                    <th>Tipo</th>
                    <th>Stato</th>
                    <th>Data arrivo</th>
                    <th class="text-end">Azioni</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pratiche as $p)
                <tr @if($p->stato == 'in_giacenza' && \Carbon\Carbon::parse($p->data_arrivo)->diffInDays(now())>=15) class="table-warning" @endif>
                    <td>{{ $p->codice }}</td>
                    <td>{{ $p->cliente_nome }}</td>
                    <td>{{ $p->caso ?? '-' }}</td>
                    <td>{{ $p->tipo_pratica }}</td>
                    <td>
                        @include('partials._state_badge', ['stato' => $p->stato])
                    </td>
                    <td>Il {{ $p->data_arrivo->format('d/m/Y') }} alle {{ $p->data_arrivo->format('H:i') }}</td>
                    <td>
                        <div  class="d-flex flex-wrap justify-content-end gap-1">
<!-- Visualizza -->
                            <form action="{{ route('admin.pratiche.show', $p->id) }}">
                                <button class="btn btn-outline-primary btn-sm icon-btn" href="#" title="Visualizza">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
<!-- Modifica -->
                            <form action="{{ route('admin.pratiche.edit', $p->id) }}">
                                <button class="btn btn-sm icon-btn" href="#" title="Modifica pratica" aria-label="Modifica pratica" style="background-color: #3B71CA">
                                    <i class="fas fa-edit text-white"></i>
                                </button>
                            </form>
<!-- Sposta in giacenza -->
                            <form method="POST" action="{{ route('admin.pratiche.giacenza', $p->id) }}" class="d-inline">
                                @csrf
                                <button type="button"
                                        class="btn btn-sm btn-secondary btn-giacenza icon-btn"
                                        data-id="{{ $p->id }}"
                                        data-codice="{{ $p->codice }}"
                                        data-cliente="{{ $p->cliente_nome }}"
                                        data-stato="{{ $p->stato }}"
                                        data-remove-url="{{ route('admin.pratiche.remove_giacenza', $p->id) }}"
                                        data-remove-method="POST"
                                        title="Metti in giacenza">
                                    <i class="fas fa-clock"></i>
                                </button>
                            </form>
<!-- sposta nel cestino -->
                            <form method="POST" action="{{ route('admin.pratiche.delete', $p->id) }}" class="d-inline">
                                @csrf
                                {{-- se usi method spoofing per DELETE --}}
                                {{-- @method('DELETE') --}}
                                <button type="button"
                                        class="btn btn-sm btn-dark btn-cestino icon-btn"
                                        data-id="{{ $p->id }}"
                                        data-codice="{{ $p->codice }}"
                                        data-cliente="{{ $p->cliente_nome }}"
                                        title="Sposta nel cestino">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
<!-- Eliminazione definitiva -->
                            <form method="POST" action="{{ route('admin.pratiche.force-delete', $p->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="btn btn-sm btn-danger btn-delete-permanent icon-btn"
                                        data-id="{{ $p->id }}"
                                        data-codice="{{ $p->codice }}"
                                        data-cliente="{{ $p->cliente_nome }}"
                                        title="Elimina definitivamente">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pag-position">
        {{ $pratiche->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // mappa azione -> testo/modal
    var ACTIONS = {
        'giacenza': {
            title: 'Mettere la pratica in giacenza',
            confirmText: 'Sì, sposta in giacenza',
            danger: false,
            bodyPrefix: 'Vuoi mettere la pratica in giacenza?'
        },
        'cestino': {
            title: 'Spostare la pratica nel cestino?',
            confirmText: 'Sì, sposta nel cestino',
            danger: false,
            bodyPrefix: 'Questa operazione sposterà la pratica nel cestino (recuperabile).'
        },
        'delete-permanent': {
            title: 'Eliminare definitivamente la pratica?',
            confirmText: 'Sì, elimina definitivamente',
            danger: true,
            bodyPrefix: 'Questa azione è <strong>irreversibile</strong>! Tutti i dati saranno rimossi definitivamente.'
        }
    };

    // helper per escape testo
    function escapeHtml(unsafe) {
        if (!unsafe && unsafe !== 0) return '';
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // helper per creare e submit form con CSRF (metodo POST o DELETE)
    function submitToUrl(url, method) {
        method = (method || 'POST').toUpperCase();
        var token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : null;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        if (token) {
            var inputToken = document.createElement('input');
            inputToken.type = 'hidden';
            inputToken.name = '_token';
            inputToken.value = token;
            form.appendChild(inputToken);
        }

        if (method !== 'POST') {
            // method spoofing
            var inputMethod = document.createElement('input');
            inputMethod.type = 'hidden';
            inputMethod.name = '_method';
            inputMethod.value = method;
            form.appendChild(inputMethod);
        }

        document.body.appendChild(form);
        form.submit();
    }

    // apre modal conferma con Tingle oppure fallback confirm
    function openConfirmModal(opts, formEl, item) {
        // se Tingle non disponibile fallback a confirm()
        if (typeof tingle === 'undefined') {
            var msg = opts.bodyPrefix + '\n\nCodice: ' + item.codice + ' — Cliente: ' + item.cliente;
            if (confirm(msg)) {
                formEl.submit();
            }
            return;
        }

        var modal = new tingle.modal({
            footer: true,
            stickyFooter: false,
            closeMethods: ['overlay', 'escape'],
            closeLabel: "Chiudi",
            cssClass: ['tingle-modal--small'],
            onClose: function() { modal.destroy(); }
        });

        var html = '<div class="px-1">';
        html += '<p class="mb-2">' + opts.bodyPrefix + '</p>';
        html += '<p class="small text-muted mb-0">Codice: <strong>' + escapeHtml(item.codice) + '</strong> — Cliente: <strong>' + escapeHtml(item.cliente) + '</strong></p>';
        html += '</div>';
        modal.setContent('<h5 class="mb-2">' + opts.title + '</h5>' + html);

        var confirmBtn = modal.addFooterBtn(opts.confirmText, opts.danger ? 'btn btn-danger' : 'btn btn-primary', function () {
            confirmBtn.setAttribute('disabled', 'disabled');
            formEl.submit();
        });

        modal.addFooterBtn('Annulla', 'btn btn-secondary ms-3', function () {
            modal.close();
        });

        modal.open();
    }

    // modal per avviso "già in giacenza" con eventuale azione di rimozione
    function openAlreadyGiacenzaModal(item, removeUrl, removeMethod) {
        if (typeof tingle === 'undefined') {
            var msg = 'La pratica ' + item.codice + ' è già in giacenza.\n\nCliente: ' + item.cliente;
            alert(msg);
            return;
        }

        var modal = new tingle.modal({
            footer: true,
            stickyFooter: false,
            closeMethods: ['overlay', 'escape'],
            closeLabel: "Chiudi",
            cssClass: ['tingle-modal--small'],
            onClose: function() { modal.destroy(); }
        });

        var html = '<div class="px-1">';
        html += '<p class="mb-2">La pratica selezionata risulta già <strong>in giacenza</strong>.</p>';
        html += '<p class="small text-muted mb-0">Codice: <strong>' + escapeHtml(item.codice) + '</strong> — Cliente: <strong>' + escapeHtml(item.cliente) + '</strong></p>';
        html += '</div>';
        modal.setContent('<h5 class="mb-2">Pratica già in giacenza</h5>' + html);

        // se abbiamo una URL per rimuovere dalla giacenza, mostriamo il pulsante che invia a quell'URL
        if (removeUrl) {
            var rmMethod = (removeMethod || 'POST').toUpperCase();
            var btnClass = 'btn btn-primary';
            modal.addFooterBtn('Rimuovi da giacenza', btnClass, function () {
                // disabilita e submit via form creato dinamicamente
                submitToUrl(removeUrl, rmMethod);
            });
        }

        modal.addFooterBtn('Chiudi', 'btn btn-secondary ms-3', function () {
            modal.close();
        });

        modal.open();
    }

    // handler generico per pulsanti con mapping
    function bindButton(selector, actionKey) {
        document.querySelectorAll(selector).forEach(function(btn) {
            btn.addEventListener('click', function (ev) {
                ev.preventDefault();
                var form = btn.closest('form');
                if (!form) return;

                // prendi dati dal data-*
                var item = {
                    id: btn.getAttribute('data-id') || '',
                    codice: btn.getAttribute('data-codice') || '',
                    cliente: btn.getAttribute('data-cliente') || '',
                    stato: btn.getAttribute('data-stato') || ''
                };

                var opts = ACTIONS[actionKey];

                // caso speciale: se vogliamo mettere in giacenza ma è già in giacenza
                if (actionKey === 'giacenza' && item.stato === 'in_giacenza') {
                    // legge attributi opzionali per rimozione
                    var removeUrl = btn.getAttribute('data-remove-url') || null;
                    var removeMethod = btn.getAttribute('data-remove-method') || 'POST';
                    openAlreadyGiacenzaModal(item, removeUrl, removeMethod);
                    return;
                }

                // comportamento normale
                openConfirmModal(opts, form, item);
            });
        });
    }

    // Bind per i tre pulsanti
    bindButton('.btn-giacenza', 'giacenza');
    bindButton('.btn-cestino', 'cestino');
    bindButton('.btn-delete-permanent', 'delete-permanent');
});
</script>
@endpush




@push('styles')
<style>
.custom-modal .tingle-btn--primary {
    border-radius: 5px;
    padding: 11px 14px;
}

.custom-modal .tingle-btn--default {
    background-color: #6c757d;
    border-color: #6c757d;
    border-radius: 5px;
    padding: 11px 14px;
}
</style>
@endpush
