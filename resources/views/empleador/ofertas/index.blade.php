@extends('layouts.app')

@section('title', 'Mis ofertas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-briefcase text-brand me-1"></i>Mis ofertas</h4>
        <a href="{{ route('empleador.ofertas.create') }}" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg me-1"></i>Nueva oferta</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Lugar</th>
                        <th>Salario</th>
                        <th>Fecha límite</th>
                        <th>Postulaciones</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($ofertas as $oferta)
                    <tr>
                        <td>{{ $oferta->titulo }}</td>
                        <td class="small">{{ $oferta->lugar }}</td>
                        <td class="small">{{ $oferta->salario ? 'S/ '.number_format($oferta->salario, 2) : '—' }}</td>
                        <td class="small">{{ $oferta->fecha_limite?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $oferta->postulaciones_count }}</span></td>
                        <td><span class="badge badge-soft-{{ $oferta->estado }}">{{ ucfirst($oferta->estado) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                            <a href="{{ route('empleador.ofertas.edit', $oferta) }}" class="btn btn-sm btn-outline-brand">Editar</a>
                            <form action="{{ route('empleador.ofertas.destroy', $oferta) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar la oferta «{{ $oferta->titulo }}»?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aún no has creado ofertas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $ofertas->links() }}</div>
@endsection
