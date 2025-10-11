@extends('layouts.navbar')

@section('content')
<div class="d-flex justify-content-center py-4">
    <div class="col-md-10 col-lg-8">
        <h3 class="mb-4">Modifica Pratica #{{ $pr->id }}</h3>

        <form method="POST" action="{{ route('admin.pratiche.update', $pr->id) }}">
            @csrf
            @method('PUT')
            @include('pratiche._form')
            <button class="btn text-white" type="submit" style="background-color: #3B71CA">Salva</button>
            <a class="btn btn-outline-secondary ms-2" href="{{ route('admin.pratiche.index') }}">Annulla</a>
        </form>
    </div>
</div>
@endsection
