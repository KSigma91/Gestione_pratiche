@extends('layouts.navbar')

@section('content')
<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-6">
            <div class="card">
                <div class="card-header">Accedi</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email"><small class="text-secondary">Email</small></label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group mt-3">
                            <label for="password"><small class="text-secondary">Password</small></label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                    name="password" required>
                            @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group form-check mt-3">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember"><small>Ricordami</small></label>
                        </div>
                        <div class="form-group mb-0 mt-3">
                            <button type="submit" class="btn btn-primary text-white">Accedi</button>
                            @if (Route::has('password.request'))
                            <a class="btn btn-link text-decoration-none" href="{{ route('password.request') }}">Password dimenticata?</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
