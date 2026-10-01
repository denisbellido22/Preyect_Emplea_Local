@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-people text-brand me-1"></i>Gestión de usuarios</h4>
        <span class="text-muted small">{{ $usuarios->total() }} usuario(s)</span>
    </div>

    <form method="GET" class="mb-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-sm {{ !$rol ? 'btn-brand' : 'btn-outline-brand' }}">Todos</a>
            <a href="{{ route('admin.usuarios.index', ['role' => 'admin']) }}" class="btn btn-sm {{ $rol === 'admin' ? 'btn-brand' : 'btn-outline-brand' }}">Administradores</a>
            <a href="{{ route('admin.usuarios.index', ['role' => 'empleador']) }}" class="btn btn-sm {{ $rol === 'empleador' ? 'btn-brand' : 'btn-outline-brand' }}">Empleadores</a>
            <a href="{{ route('admin.usuarios.index', ['role' => 'postulante']) }}" class="btn btn-sm {{ $rol === 'postulante' ? 'btn-brand' : 'btn-outline-brand' }}">Postulantes</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Ofertas</th>
                        <th>Postulaciones</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($usuarios as $u)
                    <tr>
                        <td>{{ $u->nombreCompleto() }}</td>
                        <td class="small">{{ $u->email }}</td>
                        <td><span class="badge bg-brand">{{ ucfirst($u->role) }}</span></td>
                        <td>{{ $u->ofertas_count }}</td>
                        <td>{{ $u->postulaciones_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.usuarios.edit', $u) }}" class="btn btn-sm btn-outline-brand">Editar</a>
                            @if($u->id !== auth()->id())
                                <form action="{{ route('admin.usuarios.destroy', $u) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar al usuario {{ $u->email }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay usuarios.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $usuarios->links() }}</div>
@endsection
