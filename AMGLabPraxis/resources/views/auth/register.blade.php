@extends('layouts.navbar')

@section('content')
<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-6">
            <div class="card">
                <div class="card-header">Registrati</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="form-group">
                            <label for="name"><small class="text-secondary">Nome</small></label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group mt-3">
                            <label for="email"><small class="text-secondary">Email</small></label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                            @error('email')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group mt-3">
                            <label for="password"><small class="text-secondary">Password</small></label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                            @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group mt-3">
                            <label for="password-confirm"><small class="text-secondary">Conferma Password</small></label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                        </div>
                        <div class="form-group mb-0 mt-3">
                            <button type="submit" class="btn btn-primary text-white">Registrati</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
