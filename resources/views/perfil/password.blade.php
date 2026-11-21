@extends('layouts.app')

@section('title', __('Cambiar contraseña'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-shield-lock text-brand me-1"></i>{{ __('Cambiar contraseña') }}</h4>
                    <p class="text-muted small mb-4">
                        {{ __('Actualiza la contraseña de tu cuenta') }}
                        (<span class="badge bg-brand">{{ __('role.'.$user->role) }}</span>).
                    </p>

                    <form action="{{ route('perfil.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">{{ __('Contraseña actual *') }}</label>
                            <div class="input-group @error('current_password') has-validation @enderror">
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       autocomplete="current-password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="current_password"
                                        aria-label="{{ __('Mostrar u ocultar la contraseña actual') }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Nueva contraseña *') }}</label>
                            <div class="input-group @error('password') has-validation @enderror">
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="new-password" minlength="8" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="password"
                                        aria-label="{{ __('Mostrar u ocultar la nueva contraseña') }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">{{ __('Mínimo 8 caracteres. Usa una contraseña que no utilices en otros sitios.') }}</div>
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-check2-circle me-1"></i>{{ __('Guardar contraseña') }}
                            </button>
                            <a href="{{ route('perfil.show') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
                        </div>
                    </form>
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
