@extends('layouts.app')

@section('title', __('Verifica tu correo electrónico'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            @if($user->hasVerifiedEmail())
                <div class="card">
                    <div class="card-body p-4 text-center">
                        <div class="display-5 text-success"><i class="bi bi-patch-check-fill"></i></div>
                        <h4 class="mt-2 mb-1">{{ __('Correo verificado') }}</h4>
                        <p class="text-muted mb-3">
                            {{ __('Tu dirección :email ya está confirmada.', ['email' => $user->email]) }}
                            {{ __('No necesitas verificar nuevamente.') }}
                        </p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="{{ route('dashboard') }}" class="btn btn-brand btn-sm">
                                <i class="bi bi-speedometer2 me-1"></i>{{ __('Ir a mi panel') }}
                            </a>
                            <a href="{{ route('perfil.show') }}" class="btn btn-outline-brand btn-sm">
                                <i class="bi bi-person-badge me-1"></i>{{ __('Ver mi perfil') }}
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body p-4">
                        <div class="text-center">
                            <div class="display-5 text-brand"><i class="bi bi-envelope-check"></i></div>
                            <h4 class="mt-2 mb-1">{{ __('Revisa tu bandeja de entrada') }}</h4>
                            <p class="text-muted mb-3">
                                {{ __('Enviamos un enlace de verificación a :email.', ['email' => $user->email]) }}
                            </p>
                        </div>

                        <ol class="small text-muted ps-3 mb-3">
                            <li>{{ __('Abre el mensaje de :app.', ['app' => config('app.name', 'Emplea Local')]) }}</li>
                            <li>{!! __('Pulsa el botón «:boton».', ['boton' => '<strong>'.__('Verificar mi correo electrónico').'</strong>']) !!}</li>
                            <li>{{ __('Volverás a tu cuenta con la dirección confirmada.') }}</li>
                        </ol>

                        <div class="alert alert-warning small mb-3" role="alert">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ __('Si no lo ves en unos minutos, revisa la carpeta de spam o promociones y, si hace falta, solicita un correo nuevo. El enlace caduca en :minutos minutos.', ['minutos' => (int) config('auth.verification.expire', 60)]) }}
                        </div>

                        <form action="{{ route('verification.send') }}" method="POST" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-envelope-arrow-up me-1"></i>{{ __('Reenviar correo de verificación') }}
                            </button>
                        </form>

                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-brand btn-sm">
                                <i class="bi bi-speedometer2 me-1"></i>{{ __('Ir a mi panel') }}
                            </a>
                            <a href="{{ route('perfil.show') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-person-badge me-1"></i>{{ __('Ver mi perfil') }}
                            </a>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="small text-muted mb-2">
                                {{ __('¿Quieres entrar con otra cuenta o terminar esta sesión?') }}
                            </p>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm text-brand p-0">
                                    <i class="bi bi-box-arrow-right me-1"></i>{{ __('Volver al inicio de sesión') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
