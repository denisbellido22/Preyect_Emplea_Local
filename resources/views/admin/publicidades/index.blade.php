@extends('layouts.app')

@section('title', __('Gestión de publicidad'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-megaphone text-brand me-1"></i>{{ __('Gestión de publicidad') }}</h4>
        <span class="text-muted small">{{ __(':count solicitud(es)', ['count' => $publicidades->total()]) }}</span>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.publicidades.index') }}" class="btn btn-sm {{ !$estado ? 'btn-brand' : 'btn-outline-brand' }}">{{ __('Todas') }}</a>
        @foreach(['pendiente', 'aprobado', 'rechazado', 'finalizado'] as $est)
            <a href="{{ route('admin.publicidades.index', ['estado' => $est]) }}" class="btn btn-sm {{ $estado === $est ? 'btn-brand' : 'btn-outline-brand' }}">{{ __('status.'.$est) }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Imagen') }}</th>
                        <th>{{ __('Título') }}</th>
                        <th>{{ __('Empleador') }}</th>
                        <th>{{ __('Vigencia') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th class="text-end">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($publicidades as $pub)
                    <tr>
                        <td>
                            @if($pub->imagen)
                                <img src="{{ asset($pub->imagen) }}" alt="{{ $pub->titulo }}" class="rounded object-cover" style="width: 64px; height: 48px;">
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ $pub->titulo }}</td>
                        <td class="small">{{ $pub->empleador?->nombreCompleto() }}</td>
                        <td class="small">
                            {{ $pub->fecha_inicio?->format('d/m/Y') ?? '—' }} - {{ $pub->fecha_fin?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td><span class="badge badge-soft-{{ $pub->estado }}">{{ __('status.'.$pub->estado) }}</span></td>
                        <td class="text-end">
                            @if($pub->estado !== 'aprobado' && $pub->estado !== 'finalizado')
                                <form action="{{ route('admin.publicidades.aprobar', $pub) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">{{ __('Aprobar') }}</button>
                                </form>
                            @endif
                            @if($pub->estado !== 'rechazado' && $pub->estado !== 'finalizado')
                                <form action="{{ route('admin.publicidades.rechazar', $pub) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">{{ __('Rechazar') }}</button>
                                </form>
                            @endif
                            @if($pub->estado === 'aprobado')
                                <form action="{{ route('admin.publicidades.finalizar', $pub) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">{{ __('Finalizar') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No hay solicitudes de publicidad.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $publicidades->links() }}</div>
@endsection
