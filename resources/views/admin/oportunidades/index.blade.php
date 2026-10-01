@extends('layouts.app')

@section('title', 'Oportunidades')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-mortarboard text-brand me-1"></i>Gestión de oportunidades</h4>
        <a href="{{ route('admin.oportunidades.create') }}" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg me-1"></i>Nueva oportunidad</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Imagen</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Institución</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($oportunidades as $o)
                    <tr>
                        <td>
                            @if($o->imagen)
                                <img src="{{ asset($o->imagen) }}" alt="{{ $o->titulo }}" class="rounded object-cover" style="width: 56px; height: 56px;">
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ $o->titulo }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($o->tipo) }}</span></td>
                        <td class="small">{{ $o->institucion ?: '—' }}</td>
                        <td class="small">{{ $o->fecha?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="badge badge-soft-{{ $o->estado }}">{{ ucfirst($o->estado) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.oportunidades.edit', $o) }}" class="btn btn-sm btn-outline-brand">Editar</a>
                            <form action="{{ route('admin.oportunidades.destroy', $o) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar la oportunidad «{{ $o->titulo }}»?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay oportunidades registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $oportunidades->links() }}</div>
@endsection
