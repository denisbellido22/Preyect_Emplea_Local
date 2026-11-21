@extends('layouts.app')

@section('title', __('Moderar ofertas'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-shield-check text-brand me-1"></i>{{ __('Moderación de ofertas') }}</h4>
        <span class="text-muted small">{{ __(':count oferta(s)', ['count' => $ofertas->total()]) }}</span>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.ofertas.index') }}" class="btn btn-sm {{ !$estado ? 'btn-brand' : 'btn-outline-brand' }}">{{ __('Todas') }}</a>
        <a href="{{ route('admin.ofertas.index', ['estado' => 'pendiente']) }}" class="btn btn-sm {{ $estado === 'pendiente' ? 'btn-brand' : 'btn-outline-brand' }}">{{ __('Pendientes') }}</a>
        <a href="{{ route('admin.ofertas.index', ['estado' => 'aprobado']) }}" class="btn btn-sm {{ $estado === 'aprobado' ? 'btn-brand' : 'btn-outline-brand' }}">{{ __('Aprobadas') }}</a>
        <a href="{{ route('admin.ofertas.index', ['estado' => 'rechazado']) }}" class="btn btn-sm {{ $estado === 'rechazado' ? 'btn-brand' : 'btn-outline-brand' }}">{{ __('Rechazadas') }}</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Título') }}</th>
                        <th>{{ __('Empleador') }}</th>
                        <th>{{ __('Lugar') }}</th>
                        <th>{{ __('Postulaciones') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th class="text-end">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($ofertas as $oferta)
                    <tr>
                        <td>{{ $oferta->titulo }}</td>
                        <td class="small">{{ $oferta->empleador?->nombreCompleto() }}</td>
                        <td class="small">{{ $oferta->lugar }}</td>
                        <td>{{ $oferta->postulaciones_count }}</td>
                        <td><span class="badge badge-soft-{{ $oferta->estado }}">{{ __('status.'.$oferta->estado) }}</span></td>
                        <td class="text-end">
                            @if($oferta->estado !== 'aprobado')
                                <form action="{{ route('admin.ofertas.aprobar', $oferta) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">{{ __('Aprobar') }}</button>
                                </form>
                            @endif
                            @if($oferta->estado !== 'rechazado')
                                <form action="{{ route('admin.ofertas.rechazar', $oferta) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">{{ __('Rechazar') }}</button>
                                </form>
                            @endif
                            <a href="{{ route('admin.ofertas.edit', $oferta) }}" class="btn btn-sm btn-outline-brand">{{ __('Editar') }}</a>
                            <form action="{{ route('admin.ofertas.destroy', $oferta) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm(this.dataset.confirm);"
                                  data-confirm="{{ __('¿Eliminar esta oferta?') }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Eliminar') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No hay ofertas.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $ofertas->links() }}</div>
@endsection
