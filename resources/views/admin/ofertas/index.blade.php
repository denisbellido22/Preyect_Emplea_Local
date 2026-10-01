@extends('layouts.app')

@section('title', 'Moderar ofertas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-shield-check text-brand me-1"></i>Moderación de ofertas</h4>
        <span class="text-muted small">{{ $ofertas->total() }} oferta(s)</span>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.ofertas.index') }}" class="btn btn-sm {{ !$estado ? 'btn-brand' : 'btn-outline-brand' }}">Todas</a>
        <a href="{{ route('admin.ofertas.index', ['estado' => 'pendiente']) }}" class="btn btn-sm {{ $estado === 'pendiente' ? 'btn-brand' : 'btn-outline-brand' }}">Pendientes</a>
        <a href="{{ route('admin.ofertas.index', ['estado' => 'aprobado']) }}" class="btn btn-sm {{ $estado === 'aprobado' ? 'btn-brand' : 'btn-outline-brand' }}">Aprobadas</a>
        <a href="{{ route('admin.ofertas.index', ['estado' => 'rechazado']) }}" class="btn btn-sm {{ $estado === 'rechazado' ? 'btn-brand' : 'btn-outline-brand' }}">Rechazadas</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Empleador</th>
                        <th>Lugar</th>
                        <th>Postulaciones</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($ofertas as $oferta)
                    <tr>
                        <td>{{ $oferta->titulo }}</td>
                        <td class="small">{{ $oferta->empleador?->nombreCompleto() }}</td>
                        <td class="small">{{ $oferta->lugar }}</td>
                        <td>{{ $oferta->postulaciones_count }}</td>
                        <td><span class="badge badge-soft-{{ $oferta->estado }}">{{ ucfirst($oferta->estado) }}</span></td>
                        <td class="text-end">
                            @if($oferta->estado !== 'aprobado')
                                <form action="{{ route('admin.ofertas.aprobar', $oferta) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Aprobar</button>
                                </form>
                            @endif
                            @if($oferta->estado !== 'rechazado')
                                <form action="{{ route('admin.ofertas.rechazar', $oferta) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">Rechazar</button>
                                </form>
                            @endif
                            <a href="{{ route('admin.ofertas.edit', $oferta) }}" class="btn btn-sm btn-outline-brand">Editar</a>
                            <form action="{{ route('admin.ofertas.destroy', $oferta) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar esta oferta?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay ofertas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $ofertas->links() }}</div>
@endsection
