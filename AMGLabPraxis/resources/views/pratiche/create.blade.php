@extends('layouts.navbar')

@section('content')
<div class="d-flex justify-content-center py-4">
    <div class="col-md-10 col-lg-8">
        <h3 class="mb-4">Nuova Pratica</h3>
        @if(session('duplicate_found_strict'))
            @php $dups = session('duplicate_list_strict', collect()); @endphp
            <div class="alert alert-warning">
                <strong>Duplicato esatto rilevato</strong>
                <p>{{ session('duplicate_message') }}</p>
                <ul class="list-group">
                    @foreach($dups as $d)
                        <li class="list-group-item">
                            <strong>{{ $d->codice ?? 'ID:'.$d->id }}</strong> — {{ $d->cliente_nome }} / {{ $d->tipo_pratica }}
                            <div class="small text-muted">Arrivo: {{ optional(\Carbon\Carbon::parse($d->data_arrivo))->format('d/m/Y H:i') }}</div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-2">
                    <form method="POST" action="{{ route('admin.pratiche.store') }}">
                        @csrf
                        <input type="hidden" name="force_duplicate" value="1">
                        {{-- reinserire gli altri campi come hidden o usare session old --}}
                        <button type="submit" class="btn btn-danger btn-sm">Crea comunque</button>
                        <a href="{{ route('admin.pratiche.create') }}" class="btn btn-outline-secondary btn-sm">Annulla</a>
                    </form>
                </div>
            </div>
        @endif

        @if(session('duplicate_found_loose'))
            @php $dups = session('duplicate_list_loose', collect()); @endphp
            <div class="alert alert-info">
                <strong>Pratiche simili trovate (tipo diverso)</strong>
                <p>{!! session('duplicate_message_loose') !!}</p>
                <ul class="list-group">
                    @foreach($dups as $d)
                        <li class="list-group-item">
                            <strong>{{ $d->codice ?? 'ID:'.$d->id }}</strong> — {{ $d->cliente_nome }} / <em>{{ $d->tipo_pratica }}</em>
                            <div class="small text-muted">Arrivo: {{ optional(\Carbon\Carbon::parse($d->data_arrivo))->format('d/m/Y H:i') }}</div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-2">
                    <form method="POST" action="{{ route('admin.pratiche.store') }}">
                        @csrf
                        <input type="hidden" name="force_duplicate" value="1">
                        {{-- reinserire gli altri campi come hidden o usare session old --}}
                        <button type="submit" class="btn btn-warning btn-sm">Crea comunque</button>
                        <a href="{{ route('admin.pratiche.create') }}" class="btn btn-outline-secondary btn-sm">Annulla</a>
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
