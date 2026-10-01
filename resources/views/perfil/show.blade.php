@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-person-badge text-brand me-1"></i>Mi perfil</h4>
        <a href="{{ route('perfil.edit') }}" class="btn btn-brand btn-sm"><i class="bi bi-pencil me-1"></i>Editar perfil</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 text-brand"><i class="bi bi-person-circle"></i></div>
                    <h5 class="mt-2 mb-1">{{ $user->nombreCompleto() }}</h5>
                    <span class="badge bg-brand">{{ ucfirst($user->role) }}</span>
                    <p class="text-muted small mt-2 mb-0">{{ $user->email }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">Datos del perfil</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nombres</dt><dd class="col-sm-8">{{ $user->nombres ?: '—' }}</dd>
                        <dt class="col-sm-4">Apellidos</dt><dd class="col-sm-8">{{ $user->apellidos ?: '—' }}</dd>
                        <dt class="col-sm-4">Correo</dt><dd class="col-sm-8">{{ $user->email }}</dd>
                        <dt class="col-sm-4">Teléfono</dt><dd class="col-sm-8">{{ $user->telefono ?: '—' }}</dd>
                        <dt class="col-sm-4">Distrito</dt><dd class="col-sm-8">{{ $user->distrito ?: '—' }}</dd>

                        @if($user->isPostulante())
                            <dt class="col-sm-4">DNI</dt><dd class="col-sm-8">{{ $user->dni ?: '—' }}</dd>
                            <dt class="col-sm-4">Profesión / ocupación</dt><dd class="col-sm-8">{{ $user->profesion ?: '—' }}</dd>
                            <dt class="col-sm-4">Experiencia</dt><dd class="col-sm-8">{{ $user->experiencia ?: '—' }}</dd>
                        @else
                            <dt class="col-sm-4">Empresa / institución</dt><dd class="col-sm-8">{{ $user->empresa ?: '—' }}</dd>
                        @endif

                        <dt class="col-sm-4">Descripción</dt><dd class="col-sm-8">{{ $user->descripcion ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-brand btn-sm">Ir al dashboard</a>
            </div>
        </div>
    </div>
@endsection
