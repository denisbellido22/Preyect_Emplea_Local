@extends('layouts.app')

@section('title', __('Iniciar sesión'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-box-arrow-in-right text-brand me-1"></i>{{ __('Iniciar sesión') }}</h4>
                    <p class="text-muted small mb-4">{{ __('Accede a tu cuenta de Emplea Local.') }}</p>

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Correo electrónico') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror" required autofocus>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Contraseña') }}</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label" for="remember">{{ __('Recordarme') }}</label>
                        </div>

                        <div class="text-end mb-3">
                            <a href="{{ route('password.request') }}" class="small text-brand">{{ __('¿Olvidaste tu contraseña?') }}</a>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-brand">{{ __('Ingresar') }}</button>
                        </div>
                    </form>

                    <p class="text-center mt-3 mb-0 small">
                        {{ __('¿No tienes cuenta?') }} <a href="{{ route('register') }}" class="text-brand">{{ __('Regístrate') }}</a>
                    </p>
                </div>
            </div>

            @if($cuentasDemo->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-body p-4">
                        <h6 class="text-muted text-uppercase small mb-2">
                            <i class="bi bi-lightning-charge text-brand me-1"></i>{{ __('Acceso rápido a cuentas de prueba') }}
                        </h6>
                        <p class="small text-muted mb-2">
                            {{ __('Solo para pruebas y demostraciones. Elige una cuenta para completar el correo: deberás escribir su contraseña para ingresar.') }}
                        </p>

                        <div class="list-group list-group-flush">
                            @foreach($cuentasDemo as $cuenta)
                                <button type="button"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 btn-cuenta-demo"
                                        data-email="{{ $cuenta->email }}">
                                    <span>
                                        <span class="fw-semibold d-block">{{ $cuenta->name }}</span>
                                        <span class="text-muted small">{{ $cuenta->email }}</span>
                                    </span>
                                    <span class="badge bg-brand">{{ __('role.'.$cuenta->role) }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <script>
                    document.querySelectorAll('.btn-cuenta-demo').forEach(function (boton) {
                        boton.addEventListener('click', function () {
                            document.getElementById('email').value = boton.dataset.email;
                            document.getElementById('password').focus();
                        });
                    });
                </script>
            @endif
        </div>
    </div>
@endsection
