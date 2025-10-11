<div class="d-flex flex-column">
<!-- Cliente -->
    <div class="form-group">
        <label for="cliente_nome"><small class="text-secondary">Cliente</small></label>
        <input type="text" class="form-control" id="cliente_nome" name="cliente_nome" value="{{ old('cliente_nome', $pr->cliente_nome ?? '') }}" required>
        @error('cliente_nome')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
<!-- Caso -->
    <div class="form-group">
        <label for="caso"><small class="text-secondary">Caso</small></label>
        <input type="text" class="form-control" id="caso" name="caso"
            value="{{ old('caso', $pr->caso ?? '') }}">
        @error('caso')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
<!-- Tipo di pratica -->
    <div class="form-group">
        <label for="tipo_pratica"><small class="text-secondary">Tipo di pratica</small></label>
        <select class="form-select rounded-1" id="tipo_pratica" name="tipo_pratica" required>
            <option value="" class="text-secondary">-- Seleziona tipo di pratica --</option>
            <option value="Tribunale Civile" {{ old('tipo_pratica', $pr->tipo_pratica ?? '') == 'Tribunale Civile' ? 'selected' : '' }}>Tribunale Civile</option>
            <option value="Tribunale Penale" {{ old('tipo_pratica', $pr->tipo_pratica ?? '') == 'Tribunale Penale' ? 'selected' : '' }}>Tribunale Penale</option>
            <option value="Giudice di Pace" {{ old('tipo_pratica', $pr->tipo_pratica ?? '') == 'Giudice di Pace' ? 'selected' : '' }}>Giudice di Pace</option>
            <option value="Tar" {{ old('tipo_pratica', $pr->tipo_pratica ?? '') == 'Tar' ? 'selected' : '' }}>Tar</option>
        </select>
        @error('tipo_pratica')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
<!-- Stato pratica -->
    <div class="form-group">
        <label for="stato"><small class="text-secondary">Stato</small></label>
        <select name="stato" id="stato" class="form-select rounded-1" required>
            @php $s = old('stato', $pr->stato ?? 'in_giacenza'); @endphp
            <option value="in_giacenza" {{ $s=='in_giacenza' ? 'selected' : '' }}>In giacenza</option>
            <option value="in_lavorazione" {{ $s=='in_lavorazione' ? 'selected' : '' }}>In lavorazione</option>
            <option value="completata" {{ $s=='completata' ? 'selected' : '' }}>Completata</option>
            <option value="annullata" {{ $s=='annullata' ? 'selected' : '' }}>Annullata</option>
        </select>
        @error('stato')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
<!-- Data e ora di registrazione pratica -->
    <div class="form-group m-0">
        <label for="data_arrivo"><small class="text-secondary">Data e ora di arrivo</small></label>
        <input id="data_arrivo" name="data_arrivo" type="datetime-local" class="form-control @error('data_arrivo') is-invalid @enderror" value="{{ old('data_arrivo', \Carbon\Carbon::now()->setTimezone(config('app.timezone'))->format('Y-m-d\TH:i')) }}">
        @error('data_arrivo')
            <small class="invalid-feedback">{{ $message }}</small>
        @enderror
        <small class="form-text text-info text-end" style="font-size: .78em">Se non cambiata, verrà registrata l'ora corrente.</small>
    </div>
<!-- Data di scadenza della pratica -->
    <div class="form-group">
        <label for="data_scadenza"><small class="text-secondary">Data di scadenza</small></label>
        <input type="date" class="form-control" id="data_scadenza" name="data_scadenza" value="{{ old('data_scadenza', isset($pr->data_scadenza) && $pr->data_scadenza ? $pr->data_scadenza->format('Y-m-d') : '') }}">
        @error('data_scadenza')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="row">
<!-- Stato fattura -->
        <div class="col-md-4">
            <div class="mb-3">
                <label for="stato_fattura"><small class="text-secondary">Stato fattura</small></label>
                <select name="stato_fattura" id="stato_fattura" class="form-select rounded-1">
                    <option value="non_emessa" {{ old('stato_fattura', $pr->stato_fattura) == 'non_emessa' ? 'selected' : '' }}>Non emessa</option>
                    <option value="emessa"     {{ old('stato_fattura', $pr->stato_fattura) == 'emessa'     ? 'selected' : '' }}>Emessa</option>
                </select>
            </div>
        </div>
<!-- Stato pagamento -->
        <div class="col-md-4">
            <div class="mb-3">
                <label for="stato_pagamento"><small class="text-secondary">Stato pagamento</small></label>
                <select name="stato_pagamento" id="stato_pagamento" class="form-select rounded-1">
                    <option value="non_pagato" {{ old('stato_pagamento', $pr->stato_pagamento) == 'non_pagato' ? 'selected' : '' }}>Non pagato</option>
                    <option value="pagato"     {{ old('stato_pagamento', $pr->stato_pagamento) == 'pagato'     ? 'selected' : '' }}>Pagato</option>
                </select>
            </div>
        </div>
    </div>
<!-- Info pratica -->
    <div class="form-group mb-3">
        <label for="note"><small class="text-secondary">Note</small></label>
        <textarea class="form-control" id="note" name="note">{{ old('note', $pr->note ?? '') }}</textarea>
        @error('note')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>
