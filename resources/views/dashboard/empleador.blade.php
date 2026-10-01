@extends('layouts.app')

@section('title', 'Dashboard Empleador')

@section('content')
    <h4 class="mb-3"><i class="bi bi-speedometer2 text-brand me-1"></i>Panel del Empleador</h4>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['ofertas'] }}</div>
                <div class="stat-label">Mis ofertas</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-warning">{{ $metricas['ofertas_pendientes'] }}</div>
                <div class="stat-label">Pendientes</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-success">{{ $metricas['ofertas_aprobadas'] }}</div>
                <div class="stat-label">Aprobadas</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="stat-value text-brand">{{ $metricas['postulaciones'] }}</div>
                <div class="stat-label">Postulaciones recibidas</div>
            </div></div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('empleador.ofertas.create') }}" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg me-1"></i>Nueva oferta</a>
        <a href="{{ route('empleador.ofertas.index') }}" class="btn btn-outline-brand btn-sm">Mis ofertas</a>
        <a href="{{ route('empleador.postulantes.index') }}" class="btn btn-outline-brand btn-sm">Postulantes</a>
        <a href="{{ route('empleador.publicidades.create') }}" class="btn btn-outline-brand btn-sm">Solicitar publicidad</a>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong>Últimas postulaciones recibidas</strong></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Postulante</th><th>Oferta</th><th>Fecha</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($ultimasPostulaciones as $p)
                    <tr>
                        <td>{{ $p->postulante?->nombreCompleto() }}</td>
                        <td>{{ $p->oferta?->titulo }}</td>
                        <td class="small">{{ $p->created_at->format('d/m/Y') }}</td>
                        <td><span class="badge badge-soft-{{ $p->estado }}">{{ ucfirst($p->estado) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Aún no has recibido postulaciones.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
