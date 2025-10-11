@extends('layouts.navbar')

@section('content')
<div class="d-flex justify-content-center py-4">
    <div class="col-md-10 col-lg-8">
        <h3 class="mb-4">Nuova Pratica</h3>
        @if(session('duplicate_found'))
            @php $dups = session('duplicate_list', collect()); @endphp
            <div class="alert alert-warning">
                <strong>Attenzione:</strong> {{ session('duplicate_message') ?? 'Trovati possibili duplicati.' }}
                <div class="mt-2">
                    <ul class="list-group">
                        @foreach($dups as $d)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $d->codice ?? 'ID:'.$d->id }}</strong>
                                    &nbsp;—&nbsp; {{ $d->cliente_nome }} / {{ $d->tipo_pratica }}
                                    @if(!empty($d->caso)) &nbsp;—&nbsp; <em>{{ $d->caso }}</em> @endif
                                    <div class="small text-muted">Arrivo: {{ optional(\Carbon\Carbon::parse($d->data_arrivo))->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="text-end">
                                    <a href="{{ route('admin.pratiche.edit', $d->id) }}" class="btn btn-sm btn-outline-primary">Apri</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-3">
                    {{-- form che riposta i dati salvati in old() incluse hidden per i campi (semplice approccio) --}}
                    <form method="POST" action="{{ url()->current() }}">
                        @csrf
                        {{-- forza la creazione/aggiornamento --}}
                        <input type="hidden" name="force_duplicate" value="1">
                        {{-- reinserisci i campi principali come hidden usando old() o i valori della request --}}
                        <input type="hidden" name="cliente_nome" value="{{ old('cliente_nome') }}">
                        <input type="hidden" name="tipo_pratica" value="{{ old('tipo_pratica') }}">
                        <input type="hidden" name="caso" value="{{ old('caso') }}">
                        <input type="hidden" name="data_arrivo" value="{{ old('data_arrivo') }}">
                        {{-- se hai altri campi obbligatori aggiungili qui come hidden --}}
                        <button type="submit" class="btn btn-danger">Crea comunque</button>
                        <a href="{{ route('admin.pratiche.create') }}" class="btn btn-outline-secondary">Annulla</a>
                    </form>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.pratiche.store') }}">
            @csrf
            @include('pratiche._form')
            <button class="btn btn-primary text-white" style="background-color: #3B71CA" type="submit">Crea</button>
            <a class="btn btn-outline-secondary ms-2" href="{{ route('admin.pratiche.index') }}">Annulla</a>
        </form>
    </div>
</div>
@endsection
