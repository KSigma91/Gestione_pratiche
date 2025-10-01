@extends('layouts.navbar')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-trash-alt me-2"></i> Cestino Pratiche</h3>
        <a class="btn btn-secondary" href="{{ route('admin.pratiche.index') }}"><i class="fas fa-arrow-left me-1"></i> Torna alla lista</a>
    </div>

    {{-- Filtro per cliente --}}
    <form method="GET" action="{{ route('admin.pratiche.trash') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="cliente" class="form-control" placeholder="Filtra per cliente" value="{{ request('cliente') }}">
            <button class="btn btn-outline-primary" type="submit">
                <i class="fas fa-search"></i> Cerca
            </button>
        </div>
    </form>

    @if($pratiche->isEmpty())
        <div class="alert alert-info">
            Non ci sono pratiche nel cestino.
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 g-3">
            @foreach($pratiche as $p)
                <div class="col">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title">{{ $p->codice }}</h5>
                                    <p class="card-text mb-1"><strong>Cliente:</strong> {{ $p->cliente_nome }}</p>
                                    <p class="card-text mb-1"><strong>Tipo:</strong> {{ $p->tipo_pratica }}</p>
                                    @if(isset($p->caso))
                                        <p class="card-text mb-1"><strong>Caso:</strong> {{ $p->caso }}</p>
                                    @endif
                                    <p class="card-text text-muted"><small>
                                        Eliminato il: {{ \Carbon\Carbon::parse($p->deleted_at)->format('d/m/Y H:i') }}
                                    </small></p>
                                </div>
                                <div class="btn-group btn-group-sm gap-2" role="group" aria-label="Azioni carta pratica">
                                    <form action="{{ route('admin.pratiche.restore', $p->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success" title="Ripristina">
                                            <i class="fas fa-undo text-white"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.pratiche.force-delete', $p->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" title="Elimina definitivamente">
                                            <i class="fas fa-trash text-white"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $pratiche->links() }}
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Funzione per creare e aprire un modale
    function openDeleteModal(form) {
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
            <h3>Eliminazione Definitiva</h3>
            <p>Sei sicuro di voler eliminare definitivamente questa pratica? Questa azione è irreversibile.</p>
        `);

        modal.addFooterBtn('Elimina', 'tingle-btn tingle-btn--danger', function () {
            form.submit();
            modal.close();
        });

        modal.addFooterBtn('Annulla', 'tingle-btn tingle-btn--default', function () {
            modal.close();
        });

        modal.open();
    }

    // Gestione dei pulsanti per l'eliminazione
    document.querySelectorAll('.btn-danger').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const form = button.closest('form');
            openDeleteModal(form);
        });
    });
});


</script>

<style>
.custom-modal .tingle-btn {
    border-radius: 5px;
    padding: 11px 14px;
}
</style>
@endsection
