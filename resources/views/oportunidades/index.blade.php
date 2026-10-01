@extends('layouts.app')

@section('title', 'Oportunidades')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-mortarboard text-brand me-1"></i>Oportunidades</h4>
        <span class="text-muted small">{{ $oportunidades->total() }} publicada(s)</span>
    </div>

    <form action="{{ route('oportunidades.index') }}" method="GET" class="mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('oportunidades.index') }}"
               class="btn btn-sm {{ !$tipo ? 'btn-brand' : 'btn-outline-brand' }}">Todas</a>
            @foreach($tipos as $t)
                <a href="{{ route('oportunidades.index', ['tipo' => $t]) }}"
                   class="btn btn-sm {{ $tipo === $t ? 'btn-brand' : 'btn-outline-brand' }}">{{ ucfirst($t) }}</a>
            @endforeach
        </div>
    </form>

    @if($oportunidades->isEmpty())
        <div class="alert alert-info">No hay oportunidades publicadas.</div>
    @else
        <div class="row g-3">
            @foreach($oportunidades as $oportunidad)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        @if($oportunidad->imagen)
                            <img src="{{ asset($oportunidad->imagen) }}" class="card-img-top object-cover" style="height: 170px;" alt="{{ $oportunidad->titulo }}">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2 align-self-start">{{ ucfirst($oportunidad->tipo) }}</span>
                            <h5 class="card-title">{{ $oportunidad->titulo }}</h5>
                            <p class="text-muted small mb-2">
                                @if($oportunidad->institucion)<i class="bi bi-building"></i> {{ $oportunidad->institucion }}<br>@endif
                                @if($oportunidad->fecha)<i class="bi bi-calendar-event"></i> {{ $oportunidad->fecha->format('d/m/Y') }}@endif
                            </p>
                            <p class="card-text flex-grow-1">{{ \Illuminate\Support\Str::limit($oportunidad->descripcion, 110) }}</p>
                            <a href="{{ route('oportunidades.show', $oportunidad) }}" class="btn btn-brand btn-sm align-self-start">Ver más</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $oportunidades->links() }}</div>
    @endif
@endsection
