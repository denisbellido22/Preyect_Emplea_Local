@extends('layouts.app')

@section('title', $oportunidad->titulo)

@section('content')
    <a href="{{ route('oportunidades.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> {{ __('Volver a oportunidades') }}
    </a>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                @if($oportunidad->imagen)
                    <img src="{{ asset($oportunidad->imagen) }}" class="card-img-top object-cover" style="max-height: 320px;" alt="{{ $oportunidad->titulo }}">
                @endif
                <div class="card-body">
                    <span class="badge bg-secondary mb-2">{{ __('opportunity_type.'.$oportunidad->tipo) }}</span>
                    <h3>{{ $oportunidad->titulo }}</h3>
                    <p>{!! nl2br(e($oportunidad->descripcion)) !!}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-building text-brand me-2"></i><strong>{{ __('Institución:') }}</strong> {{ $oportunidad->institucion ?: '—' }}</li>
                        <li class="mb-2"><i class="bi bi-calendar-event text-brand me-2"></i><strong>{{ __('Fecha:') }}</strong> {{ $oportunidad->fecha?->format('d/m/Y') ?? '—' }}</li>
                        <li class="mb-2"><i class="bi bi-geo-alt text-brand me-2"></i><strong>{{ __('Lugar:') }}</strong> {{ $oportunidad->lugar ?: '—' }}</li>
                        @if($oportunidad->enlace)
                            <li class="mb-2">
                                <i class="bi bi-link-45deg text-brand me-2"></i>
                                <a href="{{ $oportunidad->enlace }}" target="_blank" rel="noopener">{{ __('Más información') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
