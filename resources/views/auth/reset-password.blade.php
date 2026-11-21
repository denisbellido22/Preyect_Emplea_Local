@extends('layouts.app')

@section('title', __('Nueva contraseña'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-shield-lock text-brand me-1"></i>{{ __('Nueva contraseña') }}</h4>
                    <p class="text-muted small mb-4">
                        {{ __('Define una contraseña nueva para tu cuenta de Emplea Local.') }}
                    </p>

                    <form action="{{ route('password.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Correo electrónico *') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $email) }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   autocomplete="email" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Nueva contraseña *') }}</label>
                            <div class="input-group @error('password') has-validation @enderror">
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="new-password" minlength="8" required autofocus>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="password"
                                        aria-label="{{ __('Mostrar u ocultar la nueva contraseña') }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">{{ __('Mínimo 8 caracteres.') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">{{ __('Confirmar nueva contraseña *') }}</label>
                            <div class="input-group @error('password_confirmation') has-validation @enderror">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control @error('password_confirmation') is-invalid @enderror"
                                       autocomplete="new-password" minlength="8" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="password_confirmation"
                                        aria-label="{{ __('Mostrar u ocultar la confirmación de la contraseña') }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">{{ __('Debe coincidir exactamente con la nueva contraseña.') }}</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-check2-circle me-1"></i>{{ __('Guardar nueva contraseña') }}
                            </button>
                        </div>
                    </form>

                    <p class="text-center mt-3 mb-0 small">
                        <a href="{{ route('login') }}" class="text-brand">{{ __('Volver a iniciar sesión') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var campo = document.getElementById(boton.dataset.target);

                if (!campo) {
                    return;
                }

                var mostrar = campo.type === 'password';
                campo.type = mostrar ? 'text' : 'password';

                var icono = boton.querySelector('i');
                icono.className = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>
@endsection
