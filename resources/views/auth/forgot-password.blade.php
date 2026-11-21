@extends('layouts.app')

@section('title', __('Recuperar contraseña'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-key text-brand me-1"></i>{{ __('Recuperar contraseña') }}</h4>
                    <p class="text-muted small mb-4">
                        {{ __('Escribe el correo con el que te registraste en Emplea Local y te enviaremos un enlace para crear una contraseña nueva.') }}
                    </p>

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Correo electrónico *') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="tucorreo@gmail.com" autocomplete="email" required autofocus>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-envelope me-1"></i>{{ __('Enviar enlace de recuperación') }}
                            </button>
                        </div>
                    </form>

                    <p class="text-center mt-3 mb-0 small">
                        <a href="{{ route('login') }}" class="text-brand">{{ __('Volver a iniciar sesión') }}</a>
                    </p>
                </div>
            </div>

            <div class="alert alert-light border mt-3 small mb-0" role="note">
                <i class="bi bi-info-circle text-brand me-1"></i>
                {{ __('El enlace del correo vence en :minutos minutos y solo puede utilizarse una vez. Revisa también tu carpeta de correo no deseado.', ['minutos' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]) }}
            </div>
        </div>
    </div>
@endsection
