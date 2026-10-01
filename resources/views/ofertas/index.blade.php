@extends('layouts.app')

@section('title', 'Ofertas laborales')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-briefcase text-brand me-1"></i>Ofertas laborales</h4>
        <span class="text-muted small">{{ $ofertas->total() }} resultado(s)</span>
    </div>

    <form action="{{ route('ofertas.index') }}" method="GET" class="card card-body mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-9">
                <label for="q" class="form-label small text-muted mb-1">Buscar por título, ubicación o tipo de trabajo</label>
                <input type="text" name="q" id="q" value="{{ $termino }}" class="form-control"
                       placeholder="Ej.: albañil, Coracora, tiempo completo...">
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-brand"><i class="bi bi-search me-1"></i>Buscar</button>
            </div>
        </div>
        @if($termino)
            <div class="mt-2">
                <a href="{{ route('ofertas.index') }}" class="small text-decoration-none">Limpiar búsqueda</a>
            </div>
        @endif
    </form>

    @if($ofertas->isEmpty())
        <div class="alert alert-info">
            No se encontraron ofertas aprobadas
            @if($termino) para el término «{{ $termino }}» @endif.
        </div>
    @else
        <div class="row g-3">
            @foreach($ofertas as $oferta)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">{{ $oferta->titulo }}</h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-building"></i> {{ $oferta->empleador?->nombreCompleto() }}
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt"></i> {{ $oferta->lugar }}
                                &nbsp;·&nbsp; <i class="bi bi-clock"></i> {{ $oferta->tipo_trabajo }}
                            </p>
                            <p class="card-text flex-grow-1">{{ \Illuminate\Support\Str::limit($oferta->descripcion, 120) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                @if($oferta->salario)
                                    <span class="fw-semibold text-brand">S/ {{ number_format($oferta->salario, 2) }}</span>
                                @else
                                    <span class="text-muted small">Salario a convenir</span>
                                @endif
                                <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-brand btn-sm">Ver detalle</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $ofertas->links() }}
        </div>
    @endif
@endsection
