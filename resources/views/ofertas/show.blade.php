@extends('layouts.app')

@section('title', $oferta->titulo)

@section('content')
    <a href="{{ route('ofertas.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Volver a ofertas
    </a>

    @if($oferta->estado !== 'aprobado')
        <div class="alert alert-warning">
            <i class="bi bi-eye me-1"></i>Esta oferta está en estado <strong>{{ ucfirst($oferta->estado) }}</strong> y no es visible públicamente.
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h3 class="mb-1">{{ $oferta->titulo }}</h3>
                        <span class="badge badge-soft-{{ $oferta->estado }}">{{ ucfirst($oferta->estado) }}</span>
                    </div>
                    <p class="text-muted mb-3">
                        <i class="bi bi-building"></i> {{ $oferta->empleador?->nombreCompleto() }}
                    </p>

                    <h6 class="text-uppercase text-muted small">Descripción</h6>
                    <p>{!! nl2br(e($oferta->descripcion)) !!}</p>

                    <h6 class="text-uppercase text-muted small mt-4">Requisitos</h6>
                    <p>{!! nl2br(e($oferta->requisitos)) !!}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-geo-alt text-brand me-2"></i><strong>Ubicación:</strong> {{ $oferta->lugar }}</li>
                        <li class="mb-2"><i class="bi bi-clock text-brand me-2"></i><strong>Tipo:</strong> {{ $oferta->tipo_trabajo }}</li>
                        <li class="mb-2">
                            <i class="bi bi-cash-coin text-brand me-2"></i><strong>Salario:</strong>
                            {{ $oferta->salario ? 'S/ '.number_format($oferta->salario, 2) : 'A convenir' }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-calendar-event text-brand me-2"></i><strong>Fecha límite:</strong>
                            {{ $oferta->fecha_limite ? $oferta->fecha_limite->format('d/m/Y') : 'No especificada' }}
                        </li>
                    </ul>
                </div>
            </div>

            @auth
                @if(auth()->user()->isPostulante())
                    @if($yaPostulo)
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle me-1"></i>Ya te postulaste a esta oferta. Revisa el estado en
                            <a href="{{ route('postulaciones.index') }}" class="alert-link">Mis postulaciones</a>.
                        </div>
                    @elseif($oferta->estado === 'aprobado')
                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-2">Postular a esta oferta</h6>
                                <form action="{{ route('postulaciones.store', $oferta) }}" method="POST">
                                    @csrf
                                    <div class="mb-2">
                                        <textarea name="mensaje" rows="3" class="form-control" placeholder="Mensaje para el empleador (opcional)">{{ old('mensaje') }}</textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-brand">Enviar postulación</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @elseif(auth()->user()->isEmpleador() && auth()->id() === $oferta->user_id)
                    <a href="{{ route('empleador.ofertas.edit', $oferta) }}" class="btn btn-outline-brand w-100">Editar mi oferta</a>
                @endif
            @else
                <div class="alert alert-info mb-0">
                    <a href="{{ route('login') }}" class="alert-link">Inicia sesión</a> como postulante para postular a esta oferta.
                </div>
            @endauth
        </div>
    </div>
@endsection
