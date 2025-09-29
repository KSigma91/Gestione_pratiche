<div class="d-flex flex-column gap-3">
    <div class="form-group">
        <label for="cliente_nome"><small class="text-secondary">Cliente</small></label>
        <input type="text" class="form-control" id="cliente_nome" name="cliente_nome" value="{{ old('cliente_nome', $pr->cliente_nome ?? '') }}" required>
        @error('cliente_nome')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="form-group">
        <label for="caso"><small class="text-secondary">Caso</small></label>
        <input type="text" class="form-control" id="caso" name="caso"
            value="{{ old('caso', $pr->caso ?? '') }}">
        @error('caso')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="form-group">
        <label for="tipo_pratica"><small class="text-secondary">Tipo di pratica</small></label>
        <select class="form-select" id="tipo_pratica" name="tipo_pratica" required>
            <option value="" class="text-secondary">-- Seleziona tipo di pratica --</option>
            <option value="Tribunale Civile" {{ old('tipo_pratica', $pr->tipo_pratica ?? '') == 'Tribunale Civile' ? 'selected' : '' }}>Tribunale Civile</option>
            <option value="Tribunale Penale" {{ old('tipo_pratica', $pr->tipo_pratica ?? '') == 'Tribunale Penale' ? 'selected' : '' }}>Tribunale Penale</option>
            <option value="Giudice di Pace" {{ old('tipo_pratica', $pr->tipo_pratica ?? '') == 'Giudice di Pace' ? 'selected' : '' }}>Giudice di Pace</option>
            <option value="Tar" {{ old('tipo_pratica', $pr->tipo_pratica ?? '') == 'Tar' ? 'selected' : '' }}>Tar</option>
        </select>
        @error('tipo_pratica')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="form-group">
        <label for="stato"><small class="text-secondary">Stato</small></label>
        <select name="stato" id="stato" class="form-select" required>
            @php $s = old('stato', $pr->stato ?? 'in_giacenza'); @endphp
            <option value="in_giacenza" {{ $s=='in_giacenza' ? 'selected' : '' }}>In giacenza</option>
            <option value="in_lavorazione" {{ $s=='in_lavorazione' ? 'selected' : '' }}>In lavorazione</option>
            <option value="completata" {{ $s=='completata' ? 'selected' : '' }}>Completata</option>
            <option value="annullata" {{ $s=='annullata' ? 'selected' : '' }}>Annullata</option>
        </select>
        @error('stato')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="form-group">
        <label for="data_arrivo"><small class="text-secondary">Data e ora di arrivo</small></label>
        <input type="datetime-local" class="form-control" id="data_arrivo" name="data_arrivo" value="{{ old('data_arrivo', isset($pr->data_arrivo) ? $pr->data_arrivo->format('Y-m-d\TH:i') : '') }}" required>
        @error('data_arrivo')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="form-group">
        <label for="data_scadenza"><small class="text-secondary">Data di scadenza</small></label>
        <input type="date" class="form-control" id="data_scadenza" name="data_scadenza" value="{{ old('data_scadenza', isset($pr->data_scadenza) && $pr->data_scadenza ? $pr->data_scadenza->format('Y-m-d') : '') }}">
        @error('data_scadenza')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="form-group mb-3">
        <label for="note"><small class="text-secondary">Note</small></label>
        <textarea class="form-control" id="note" name="note">{{ old('note', $pr->note ?? '') }}</textarea>
        @error('note')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>
