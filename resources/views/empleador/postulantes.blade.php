@extends('layouts.app')

@section('title', 'Postulantes')

@section('content')
    <h4 class="mb-3"><i class="bi bi-people text-brand me-1"></i>Postulantes a mis ofertas</h4>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Postulante</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Oferta</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-end">Actualizar estado</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($postulaciones as $p)
                    <tr>
                        <td>
                            {{ $p->postulante?->nombreCompleto() }}
                            @if($p->postulante?->profesion)
                                <div class="text-muted small">{{ $p->postulante->profesion }}</div>
                            @endif
                        </td>
                        <td class="small">{{ $p->postulante?->email }}</td>
                        <td class="small">{{ $p->postulante?->telefono ?: '—' }}</td>
                        <td class="small">{{ $p->oferta?->titulo }}</td>
                        <td class="small">{{ $p->created_at->format('d/m/Y') }}</td>
                        <td><span class="badge badge-soft-{{ $p->estado }}">{{ ucfirst($p->estado) }}</span></td>
                        <td class="text-end">
                            <form action="{{ route('empleador.postulaciones.estado', $p) }}" method="POST" class="d-flex gap-1 justify-content-end">
                                @csrf
                                @method('PUT')
                                <select name="estado" class="form-select form-select-sm" style="width: auto;">
                                    @foreach(['pendiente', 'revisada', 'aceptada', 'rechazada'] as $estado)
                                        <option value="{{ $estado }}" {{ $p->estado === $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-brand">Guardar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay postulaciones en tus ofertas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $postulaciones->links() }}</div>
@endsection
