@extends('layouts.app')

@section('title', __('Dashboard Postulante'))

@section('content')
    <h4 class="mb-3"><i class="bi bi-speedometer2 text-brand me-1"></i>{{ __('Panel del Postulante') }}</h4>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['postulaciones'] }}</div>
                <div class="stat-label">{{ __('Mis postulaciones') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-warning">{{ $metricas['postulaciones_pendientes'] }}</div>
                <div class="stat-label">{{ __('Pendientes') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-success">{{ $metricas['postulaciones_aceptadas'] }}</div>
                <div class="stat-label">{{ __('Aceptadas') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['ofertas_disponibles'] }}</div>
                <div class="stat-label">{{ __('Ofertas disponibles') }}</div>
            </div></div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('ofertas.index') }}" class="btn btn-brand btn-sm"><i class="bi bi-search me-1"></i>{{ __('Buscar ofertas') }}</a>
        <a href="{{ route('postulaciones.index') }}" class="btn btn-outline-brand btn-sm">{{ __('Mis postulaciones') }}</a>
        <a href="{{ route('oportunidades.index') }}" class="btn btn-outline-brand btn-sm">{{ __('Oportunidades') }}</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white"><strong>{{ __('Mis últimas postulaciones') }}</strong></div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light"><tr><th>{{ __('Oferta') }}</th><th>{{ __('Estado') }}</th></tr></thead>
                        <tbody>
                        @forelse($misPostulaciones as $p)
                            <tr>
                                <td>{{ $p->oferta?->titulo }}</td>
                                <td><span class="badge badge-soft-{{ $p->estado }}">{{ __('status.'.$p->estado) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">{{ __('Todavía no te has postulado.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white"><strong>{{ __('Ofertas recientes') }}</strong></div>
                <ul class="list-group list-group-flush">
                    @forelse($ofertasRecientes as $oferta)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('ofertas.show', $oferta) }}" class="text-decoration-none text-brand">{{ $oferta->titulo }}</a>
                            <span class="badge bg-light text-dark">{{ __('job_type.'.$oferta->tipo_trabajo) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">{{ __('No hay ofertas aprobadas.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
