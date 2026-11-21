@extends('layouts.app')

@section('title', __('Mi perfil'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-person-badge text-brand me-1"></i>{{ __('Mi perfil') }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('perfil.edit') }}" class="btn btn-brand btn-sm"><i class="bi bi-pencil me-1"></i>{{ __('Editar perfil') }}</a>
            <a href="{{ route('perfil.password.edit') }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-shield-lock me-1"></i>{{ __('Cambiar contraseña') }}</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 text-brand"><i class="bi bi-person-circle"></i></div>
                    <h5 class="mt-2 mb-1">{{ $user->nombreCompleto() }}</h5>
                    <span class="badge bg-brand">{{ __('role.'.$user->role) }}</span>
                    <p class="text-muted small mt-2 mb-0">{{ $user->email }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">{{ __('Datos del perfil') }}</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('Nombres') }}</dt><dd class="col-sm-8">{{ $user->nombres ?: '—' }}</dd>
                        <dt class="col-sm-4">{{ __('Apellidos') }}</dt><dd class="col-sm-8">{{ $user->apellidos ?: '—' }}</dd>
                        <dt class="col-sm-4">{{ __('Correo') }}</dt><dd class="col-sm-8">{{ $user->email }}</dd>
                        <dt class="col-sm-4">{{ __('Teléfono') }}</dt><dd class="col-sm-8">{{ $user->telefono ?: '—' }}</dd>
                        <dt class="col-sm-4">{{ __('Distrito') }}</dt><dd class="col-sm-8">{{ $user->distrito ?: '—' }}</dd>

                        @if($user->isPostulante())
                            <dt class="col-sm-4">{{ __('DNI') }}</dt><dd class="col-sm-8">{{ $user->dni ?: '—' }}</dd>
                            <dt class="col-sm-4">{{ __('Profesión / ocupación') }}</dt><dd class="col-sm-8">{{ $user->profesion ?: '—' }}</dd>
                            <dt class="col-sm-4">{{ __('Experiencia') }}</dt><dd class="col-sm-8">{{ $user->experiencia ?: '—' }}</dd>
                        @else
                            <dt class="col-sm-4">{{ __('Empresa / institución') }}</dt><dd class="col-sm-8">{{ $user->empresa ?: '—' }}</dd>
                        @endif

                        <dt class="col-sm-4">{{ __('Descripción') }}</dt><dd class="col-sm-8">{{ $user->descripcion ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card mt-3" id="verificacion-correo">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">
                        <i class="bi bi-envelope-check me-1"></i>{{ __('Verificación de correo electrónico') }}
                    </h6>

                    @if($user->hasVerifiedEmail())
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge badge-soft-aprobado">
                                <i class="bi bi-patch-check-fill me-1"></i>{{ __('Correo verificado') }}
                            </span>
                            <span class="text-muted">{{ $user->email }}</span>
                        </div>
                        <p class="small text-muted mb-0">
                            {{ __('Confirmamos tu dirección el :fecha.', ['fecha' => optional($user->email_verified_at)->format('d/m/Y H:i')]) }}
                            {{ __('No necesitas verificar nuevamente.') }}
                        </p>
                    @else
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge badge-soft-pendiente">
                                <i class="bi bi-clock-history me-1"></i>{{ __('Correo pendiente de verificación') }}
                            </span>
                            <span class="text-muted">{{ $user->email }}</span>
                        </div>
                        <p class="small text-muted mb-3">
                            {{ __('Confirma que esta dirección te pertenece abriendo el enlace que te enviamos. Sin verificar, no podrás publicar ofertas, solicitar publicidad ni postularte a un empleo.') }}
                        </p>
                        <form action="{{ route('verification.send') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-brand btn-sm">
                                <i class="bi bi-envelope-arrow-up me-1"></i>{{ __('Reenviar correo de verificación') }}
                            </button>
                        </form>
                        <a href="{{ route('verification.notice') }}" class="btn btn-link btn-sm text-brand">
                            {{ __('Ver instrucciones') }}
                        </a>
                    @endif
                </div>
            </div>

            @if(! $user->isAdmin())
            <div class="card border-danger-subtle mt-3" id="eliminar-cuenta">
                <div class="card-body">
                    <h6 class="text-danger text-uppercase small mb-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ __('Zona de riesgo') }}
                    </h6>
                    <p class="mb-2">
                        {{ __('Eliminar tu cuenta es una acción') }} <strong>{{ __('permanente e irreversible') }}</strong>{{ __(': no podrás volver a iniciar sesión con ella y tus datos asociados se perderán.') }}
                    </p>

                    @if(! empty($afectados))
                        <p class="small text-muted mb-1">{{ __('También se eliminarán:') }}</p>
                        <ul class="small text-muted">
                            @foreach($afectados as $afectado)
                                <li>{{ $afectado }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalEliminarCuenta">
                        <i class="bi bi-trash me-1"></i>{{ __('Eliminar mi cuenta') }}
                    </button>
                </div>
            </div>
            @endif

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-brand btn-sm">{{ __('Ir al dashboard') }}</a>
            </div>
        </div>
    </div>

    @if(! $user->isAdmin())
    <div class="modal fade" id="modalEliminarCuenta" tabindex="-1" aria-labelledby="modalEliminarCuentaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('perfil.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title text-danger" id="modalEliminarCuentaLabel">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ __('Eliminar mi cuenta') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Cerrar') }}"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-3">
                            {{ __('Esta acción es definitiva y no se puede deshacer. Para confirmar, escribe tu contraseña actual y marca la casilla de confirmación.') }}
                        </p>

                        <div class="mb-3">
                            <label for="current_password_delete" class="form-label">{{ __('Contraseña actual *') }}</label>
                            <input type="password" name="current_password" id="current_password_delete"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   autocomplete="current-password" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check">
                            <input class="form-check-input @error('confirmar_eliminacion') is-invalid @enderror"
                                   type="checkbox" name="confirmar_eliminacion" id="confirmar_eliminacion" value="1" required>
                            <label class="form-check-label" for="confirmar_eliminacion">
                                {{ __('Entiendo que mi cuenta y mis datos asociados se eliminarán de forma permanente.') }}
                            </label>
                            @error('confirmar_eliminacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>{{ __('Sí, eliminar mi cuenta') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @endif

    @if(! $user->isAdmin() && ($errors->has('current_password') || $errors->has('confirmar_eliminacion')))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                new bootstrap.Modal(document.getElementById('modalEliminarCuenta')).show();
            });
        </script>
    @endif
@endsection
