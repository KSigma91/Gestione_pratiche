@extends('layouts.navbar')

@section('content')
<div class="d-flex justify-content-center">
    <div class="col-lg-8">
        <h3 class="mb-4">Nuova Pratica</h3>

        <form method="POST" action="{{ route('admin.pratiche.store') }}">
            @csrf
            @include('pratiche._form')
            <button class="btn btn-primary text-white" style="background-color: #3B71CA" type="submit">Crea</button>
            <a class="btn btn-outline-secondary ms-2" href="{{ route('admin.pratiche.index') }}">Annulla</a>
        </form>
    </div>
</div>
@endsection
