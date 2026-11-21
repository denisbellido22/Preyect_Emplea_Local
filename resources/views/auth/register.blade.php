@extends('layouts.app')

@section('title', __('Registrarse'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-person-plus text-brand me-1"></i>{{ __('Crear cuenta') }}</h4>
                    <p class="text-muted small mb-4">{{ __('Regístrate como Empleador o Postulante.') }}</p>

                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nombre completo') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label for="email" class="form-label">{{ __('Correo electrónico') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="telefono" class="form-label">{{ __('Teléfono') }}</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                                       class="form-control @error('telefono') is-invalid @enderror">
                                @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">{{ __('Quiero registrarme como') }}</label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>{{ __('Selecciona una opción') }}</option>
                                <option value="empleador" {{ old('role') === 'empleador' ? 'selected' : '' }}>{{ __('Empleador — publicar ofertas y publicidad') }}</option>
                                <option value="postulante" {{ old('role') === 'postulante' ? 'selected' : '' }}>{{ __('Postulante — buscar ofertas y postular') }}</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">{{ __('El rol de Administrador no está disponible en el registro público.') }}</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">{{ __('Contraseña') }}</label>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">{{ __('Confirmar contraseña') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-brand">{{ __('Registrarme') }}</button>
                        </div>
                    </form>

                    <p class="text-center mt-3 mb-0 small">
                        {{ __('¿Ya tienes cuenta?') }} <a href="{{ route('login') }}" class="text-brand">{{ __('Inicia sesión') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
