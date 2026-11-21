@extends('layouts.app')

@section('title', __('Mi publicidad'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-megaphone text-brand me-1"></i>{{ __('Mis solicitudes de publicidad') }}</h4>
        <a href="{{ route('empleador.publicidades.create') }}" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg me-1"></i>{{ __('Nueva solicitud') }}</a>
    </div>

    <div class="row g-3">
        @forelse($publicidades as $pub)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    @if($pub->imagen)
                        <img src="{{ asset($pub->imagen) }}" class="card-img-top object-cover" style="height: 160px;" alt="{{ $pub->titulo }}">
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="card-title mb-1">{{ $pub->titulo }}</h6>
                            <span class="badge badge-soft-{{ $pub->estado }}">{{ __('status.'.$pub->estado) }}</span>
                        </div>
                        <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($pub->descripcion, 100) }}</p>
                        <p class="small mb-0 text-muted">
                            <i class="bi bi-calendar"></i>
                            {{ $pub->fecha_inicio?->format('d/m/Y') ?? '—' }} - {{ $pub->fecha_fin?->format('d/m/Y') ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">{{ __('Aún no has registrado solicitudes de publicidad.') }}</div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $publicidades->links() }}</div>
@endsection
