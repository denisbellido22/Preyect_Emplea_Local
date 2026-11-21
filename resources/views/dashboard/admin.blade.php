@extends('layouts.app')

@section('title', __('Dashboard Administrador'))

@section('content')
    <h4 class="mb-3"><i class="bi bi-speedometer2 text-brand me-1"></i>{{ __('Panel del Administrador') }}</h4>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['usuarios'] }}</div>
                <div class="stat-label">{{ __('Usuarios') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['ofertas'] }}</div>
                <div class="stat-label">{{ __('Ofertas') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['postulaciones'] }}</div>
                <div class="stat-label">{{ __('Postulaciones') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['oportunidades'] }}</div>
                <div class="stat-label">{{ __('Oportunidades') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['publicidades'] }}</div>
                <div class="stat-label">{{ __('Anuncios') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-warning">{{ $metricas['ofertas_pendientes'] }}</div>
                <div class="stat-label">{{ __('Ofertas pend.') }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted text-uppercase small">{{ __('Usuarios por rol') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between"><span>{{ __('Administradores') }}</span><strong>{{ $metricas['administradores'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>{{ __('Empleadores') }}</span><strong>{{ $metricas['empleadores'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>{{ __('Postulantes') }}</span><strong>{{ $metricas['postulantes'] }}</strong></li>
                </ul>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted text-uppercase small">{{ __('Ofertas por estado') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between"><span>{{ __('Pendientes') }}</span><strong>{{ $metricas['ofertas_pendientes'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>{{ __('Aprobadas') }}</span><strong>{{ $metricas['ofertas_aprobadas'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>{{ __('Rechazadas') }}</span><strong>{{ $metricas['ofertas_rechazadas'] }}</strong></li>
                </ul>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted text-uppercase small">{{ __('Publicidad') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between"><span>{{ __('Total') }}</span><strong>{{ $metricas['publicidades'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>{{ __('Pendientes') }}</span><strong>{{ $metricas['publicidades_pendientes'] }}</strong></li>
                </ul>
                <a href="{{ route('admin.publicidades.index') }}" class="btn btn-sm btn-outline-brand mt-2">{{ __('Gestionar publicidad') }}</a>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>{{ __('Últimas ofertas') }}</strong>
                    <a href="{{ route('admin.ofertas.index') }}" class="btn btn-sm btn-outline-brand">{{ __('Moderar') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light"><tr><th>{{ __('Título') }}</th><th>{{ __('Empleador') }}</th><th>{{ __('Estado') }}</th></tr></thead>
                        <tbody>
                        @forelse($ultimasOfertas as $oferta)
                            <tr>
                                <td>{{ $oferta->titulo }}</td>
                                <td class="small">{{ $oferta->empleador?->nombreCompleto() }}</td>
                                <td><span class="badge badge-soft-{{ $oferta->estado }}">{{ __('status.'.$oferta->estado) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">{{ __('Sin ofertas registradas.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white"><strong>{{ __('Últimas solicitudes de publicidad') }}</strong></div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light"><tr><th>{{ __('Título') }}</th><th>{{ __('Estado') }}</th></tr></thead>
                        <tbody>
                        @forelse($ultimasPublicidades as $pub)
                            <tr>
                                <td>{{ $pub->titulo }}</td>
                                <td><span class="badge badge-soft-{{ $pub->estado }}">{{ __('status.'.$pub->estado) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">{{ __('Sin solicitudes.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
