@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-box-arrow-in-right text-brand me-1"></i>Iniciar sesión</h4>
                    <p class="text-muted small mb-4">Accede a tu cuenta de Emplea Local.</p>

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror" required autofocus>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-brand">Ingresar</button>
                        </div>
                    </form>

                    <p class="text-center mt-3 mb-0 small">
                        ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-brand">Regístrate</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
