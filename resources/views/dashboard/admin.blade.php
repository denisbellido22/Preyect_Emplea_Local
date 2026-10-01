@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('content')
    <h4 class="mb-3"><i class="bi bi-speedometer2 text-brand me-1"></i>Panel del Administrador</h4>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['usuarios'] }}</div>
                <div class="stat-label">Usuarios</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['ofertas'] }}</div>
                <div class="stat-label">Ofertas</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['postulaciones'] }}</div>
                <div class="stat-label">Postulaciones</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['oportunidades'] }}</div>
                <div class="stat-label">Oportunidades</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['publicidades'] }}</div>
                <div class="stat-label">Anuncios</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-warning">{{ $metricas['ofertas_pendientes'] }}</div>
                <div class="stat-label">Ofertas pend.</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted text-uppercase small">Usuarios por rol</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between"><span>Administradores</span><strong>{{ $metricas['administradores'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>Empleadores</span><strong>{{ $metricas['empleadores'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>Postulantes</span><strong>{{ $metricas['postulantes'] }}</strong></li>
                </ul>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted text-uppercase small">Ofertas por estado</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between"><span>Pendientes</span><strong>{{ $metricas['ofertas_pendientes'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>Aprobadas</span><strong>{{ $metricas['ofertas_aprobadas'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>Rechazadas</span><strong>{{ $metricas['ofertas_rechazadas'] }}</strong></li>
                </ul>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted text-uppercase small">Publicidad</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between"><span>Total</span><strong>{{ $metricas['publicidades'] }}</strong></li>
                    <li class="d-flex justify-content-between"><span>Pendientes</span><strong>{{ $metricas['publicidades_pendientes'] }}</strong></li>
                </ul>
                <a href="{{ route('admin.publicidades.index') }}" class="btn btn-sm btn-outline-brand mt-2">Gestionar publicidad</a>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Últimas ofertas</strong>
                    <a href="{{ route('admin.ofertas.index') }}" class="btn btn-sm btn-outline-brand">Moderar</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light"><tr><th>Título</th><th>Empleador</th><th>Estado</th></tr></thead>
                        <tbody>
                        @forelse($ultimasOfertas as $oferta)
                            <tr>
                                <td>{{ $oferta->titulo }}</td>
                                <td class="small">{{ $oferta->empleador?->nombreCompleto() }}</td>
                                <td><span class="badge badge-soft-{{ $oferta->estado }}">{{ ucfirst($oferta->estado) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Sin ofertas registradas.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white"><strong>Últimas solicitudes de publicidad</strong></div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light"><tr><th>Título</th><th>Estado</th></tr></thead>
                        <tbody>
                        @forelse($ultimasPublicidades as $pub)
                            <tr>
                                <td>{{ $pub->titulo }}</td>
                                <td><span class="badge badge-soft-{{ $pub->estado }}">{{ ucfirst($pub->estado) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Sin solicitudes.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
