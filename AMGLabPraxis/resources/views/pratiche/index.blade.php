@extends('layouts.navbar')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Lista Pratiche</h3>
        <a class="btn btn-outline-success" href="{{ route('admin.pratiche.create') }}" title="Aggiungiere pratica" aria-label="Aggiungiere pratica"><i class="fas fa-plus me-1"></i> Aggiungi pratica</i></a>
        <a class="btn btn-primary text-white" href="#" onclick="location.reload();"><i class="fas fa-sync me-2"></i>Aggiorna</a>
    </div>

    <form method="GET" class="form-inline mb-3">
        <input type="text" name="cliente" class="form-control mr-2 my-2" placeholder="Cliente" value="{{ request('cliente') }}">
        <select name="stato" class="form-select mr-2 my-2">
            <option value="tutti" {{ request('stato') == 'tutti' ? 'selected' : '' }}>Tutti gli stati</option>
            <option value="in_giacenza" {{ request('stato') == 'in_giacenza' ? 'selected':'' }}>In giacenza</option>
            <option value="in_lavorazione" {{ request('stato') == 'in_lavorazione' ? 'selected':'' }}>In lavorazione</option>
            <option value="completata" {{ request('stato') == 'completata' ? 'selected':'' }}>Completata</option>
            <option value="annullata" {{ request('stato') == 'annullata' ? 'selected':'' }}>Annullata</option>
        </select>
        <button class="btn btn-secondary px-4" title="Filtra" aria-label="Filtra" type="submit"><i class="fas fa-sort-amount-down"></i></button>
    </form>

    <table class="table table-light table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Codice</th>
                <th>Cliente</th>
                <th>Caso</th>
                <th>Tipo</th>
                <th>Stato</th>
                <th>Data arrivo</th>
                <th>Alert</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody class="lh-lg">
            @foreach($pratiche as $p)
            <tr @if($p->stato == 'in_giacenza' && \Carbon\Carbon::parse($p->data_arrivo)->diffInDays(now())>=15) class="table-warning" @endif>
                <td>{{ $p->id }}</td>
                <td>{{ $p->codice }}</td>
                <td>{{ $p->cliente_nome }}</td>
                <td>{{ $p->caso ?? '-' }}</td>

                <td>{{ $p->tipo_pratica }}</td>
                <td>
                    @switch($p->stato)
                        @case('in_giacenza')
                        <span class="badge bg-warning text-dark">In giacenza</span>
                        @break
                        @case('in_lavorazione')
                        <span class="badge bg-info text-dark">In lavorazione</span>
                        @break
                        @case('completata')
                        <span class="badge bg-success">Completata</span>
                        @break
                        @case('annullata')
                        <span class="badge bg-secondary">Annullata</span>
                        @break
                        @default
                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $p->stato)) }}</span>
                    @endswitch
                </td>
                <td>Il {{ $p->data_arrivo->format('d/m/Y') }} alle {{ $p->data_arrivo->format('H:i') }}</td>
                <td>{{ $p->alerted ? 'SI' : 'NO' }}</td>
                <td>
<!-- Modifica -->
                    <a class="btn btn-sm icon-btn my-1" href="{{ route('admin.pratiche.edit', $p->id) }}" title="Modifica pratica" aria-label="Modifica pratica" style="display:inline-block; margin-right: 4px; background-color: #3B71CA">
                        <i class="fas fa-pen-square text-white"></i>
                    </a>
<!-- Sposta in giacenza -->
                    <form action="{{ route('admin.pratiche.giacenza', $p->id) }}" method="POST" style="display:inline-block; margin-right: 4px;">
                        @csrf
                        <button class="btn btn-sm btn-secondary btn-action icon-btn my-1 text-white" type="submit" data-action="giacenza" title="Metti in giacenza" aria-label="Metti in giacenza">
                            <i class="fas fa-pause"></i>
                        </button>
                    </form>
<!-- sposta nel cestino -->
                    <form action="{{ route('admin.pratiche.delete', $p->id) }}" method="POST" style="display:inline-block; margin-right: 4px;">
                        @csrf
                        <button class="btn btn-sm btn-dark btn-action icon-btn my-1" type="submit" data-action="cestino" title="Sposta nel cestino" aria-label="Sposta nel cestino">
                            <i class="fas fa-trash-restore-alt"></i>
                        </button>
                    </form>
<!-- Eliminazione definitiva -->
                    <form action="{{ route('admin.pratiche.force-delete', $p->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button class="btn btn-sm btn-danger btn-action icon-btn my-1 text-white" type="submit" data-action="eliminazione" title="Elimina definitivamente" aria-label="Elimina definitivamente">
                            <i class="fas fa-minus"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $pratiche->links() }}
@endsection

@push('scripts')
<script>
(function($) {
    $(document).ready(function() {
        $(document).on('click', '.btn-schedule', function(e) {
            e.preventDefault();

            var btn = $(this);
            var id = btn.data('id');
            var codice = btn.data('codice') || '';

            if (!id) {
                console.error('btn-schedule: manca data-id');
                return;
            }
            // Imposta info modal
            $('#modalPraticaInfo').text('Pratica: ' + codice + ' (id: ' + id + ')');

            // Imposta action del form (route: /admin/pratiche/{id}/schedule-delete)
            var action = '/admin/pratiche/' + id + '/schedule-delete';

            $('#scheduleForm').attr('action', '/admin/pratiche/' + id + '/schedule-delete');
            // Reset input precedente
            $('#scheduleForm')[0].reset();
            // Mostra modal con bootstrap
            $('#scheduleModal').modal('show');
        });
        // Optional: se vuoi validare lato client prima dell'invio (es. datetime non vuoto)
        $('#scheduleForm').on('submit', function(e){
            var v = $('#delete_scheduled_at').val();
            if (!v) {
                e.preventDefault();
                alert('Inserisci la data e ora di eliminazione.');
                return false;
            }
            return true;
        });
        // Debug: mostra console se ci sono errori JS
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Funzione per creare e aprire un modale
        function openModal(title, message, confirmCallback) {
            const modal = new tingle.modal({
                footer: true,
                stickyFooter: false,
                closeLabel: 'Chiudi',
                cssClass: ['custom-modal'],
                onClose: function () {
                    modal.destroy();
                }
            });

            modal.setContent(`
                <h3>${title}</h3>
                <p>${message}</p>
            `);


            modal.addFooterBtn('Conferma', 'tingle-btn tingle-btn--primary', function () {
                confirmCallback();
                modal.close();
            });

            modal.addFooterBtn('Annulla', 'tingle-btn tingle-btn--default', function () {
                modal.close();
            });

            modal.open();
        }
        // Gestione dei pulsanti per le diverse azioni
        document.querySelectorAll('.btn-action').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                const action = button.dataset.action;
                const form = button.closest('form');

                switch (action) {
                    case 'giacenza':
                        openModal(
                            'Mettere in Giacenza',
                            'Sei sicuro di voler mettere questa pratica in giacenza?',
                            function () {
                                form.submit();
                            }
                        );
                        break;
                    case 'cestino':
                        openModal(
                            'Spostare nel Cestino',
                            'Sei sicuro di voler spostare questa pratica nel cestino?',
                            function () {
                                form.submit();
                            }
                        );
                        break;
                    case 'eliminazione':
                        openModal(
                            'Eliminazione Definitiva',
                            'Sei sicuro di voler eliminare definitivamente questa pratica? Questa azione è irreversibile.',
                            function () {
                                form.submit();
                            }
                        );
                        break;
                    default:
                        console.warn('Azione non riconosciuta:', action);
                }
            });
        });
    });
})(jQuery);
</script>

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
