@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Lista Pratiche</h3>
    <a class="btn btn-primary" href="#" onclick="location.reload();">Aggiorna</a>
</div>

<form method="GET" class="form-inline mb-3">
    <input type="text" name="cliente" class="form-control mr-2" placeholder="Cliente" value="{{ request('cliente') }}">
    <select name="stato" class="form-control mr-2">
        <option value="">Tutti gli stati</option>
        <option value="in_giacenza" {{ request('stato')=='in_giacenza'?'selected':'' }}>In giacenza</option>
        <option value="in_lavorazione" {{ request('stato')=='in_lavorazione'?'selected':'' }}>In lavorazione</option>
        <option value="completata" {{ request('stato')=='completata'?'selected':'' }}>Completata</option>
        <option value="annullata" {{ request('stato')=='annullata'?'selected':'' }}>Annullata</option>
    </select>
    <button class="btn btn-secondary" type="submit">Filtra</button>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Codice</th>
            <th>Cliente</th>
            <th>Tipo</th>
            <th>Stato</th>
            <th>Data arrivo</th>
            <th>Alert</th>
            <th>Delete sched</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pratiche as $p)
        <tr @if($p->stato=='in_giacenza' && \Carbon\Carbon::parse($p->data_arrivo)->diffInDays(now())>=15) class="table-warning" @endif>
            <td>{{ $p->id }}</td>
            <td>{{ $p->codice }}</td>
            <td>{{ $p->cliente_nome }}</td>
            <td>{{ $p->tipo_pratica }}</td>
            <td>{{ $p->stato }}</td>
            <td>{{ $p->data_arrivo }}</td>
            <td>{{ $p->alerted ? 'SI' : 'NO' }}</td>
            <td>{{ $p->delete_scheduled_at ?? '-' }}</td>
            <td>
                <form action="{{ route('admin.pratiche.delete', $p->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Eliminare la pratica (soft delete)?')">Elimina</button>
                </form>

                <form action="{{ route('admin.pratiche.force-delete', $p->id) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Eliminazione definitiva?');">
                    @csrf
                    <button class="btn btn-sm btn-dark" type="submit">Elimina Definitiva</button>
                </form>

                <button class="btn btn-sm btn-info" style="margin-left:6px;" data-toggle="modal" data-target="#scheduleModal" data-id="{{ $p->id }}" data-codice="{{ $p->codice }}">Programma Eliminazione</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $pratiche->links() }}

<!-- Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <form id="scheduleForm" method="POST">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="scheduleModalLabel">Programma Eliminazione</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p id="modalPraticaInfo"></p>
            <div class="form-group">
            <label for="delete_scheduled_at">Data e ora di eliminazione (YYYY-MM-DD HH:MM:SS)</label>
            <input type="text" class="form-control" name="delete_scheduled_at" id="delete_scheduled_at" placeholder="2025-10-01 12:00:00" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
            <button type="submit" class="btn btn-primary">Programma</button>
        </div>
        </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$('#scheduleModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var id = button.data('id');
  var codice = button.data('codice');
  var modal = $(this);
  modal.find('#modalPraticaInfo').text('Pratica: ' + codice + ' (id: ' + id + ')');
  var form = modal.find('#scheduleForm');
  form.attr('action', '/admin/pratiche/' + id + '/schedule-delete');
});
</script>
@endpush
