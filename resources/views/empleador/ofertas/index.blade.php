@extends('layouts.app')

@section('title', __('Mis ofertas'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-briefcase text-brand me-1"></i>{{ __('Mis ofertas') }}</h4>
        <a href="{{ route('empleador.ofertas.create') }}" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg me-1"></i>{{ __('Nueva oferta') }}</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Título') }}</th>
                        <th>{{ __('Lugar') }}</th>
                        <th>{{ __('Salario') }}</th>
                        <th>{{ __('Fecha límite') }}</th>
                        <th>{{ __('Postulaciones') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th class="text-end">{{ __('Acciones') }}</th>
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
                        <td><span class="badge badge-soft-{{ $oferta->estado }}">{{ __('status.'.$oferta->estado) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-sm btn-outline-secondary">{{ __('Ver') }}</a>
                            <a href="{{ route('empleador.ofertas.edit', $oferta) }}" class="btn btn-sm btn-outline-brand">{{ __('Editar') }}</a>
                            <form action="{{ route('empleador.ofertas.destroy', $oferta) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm(this.dataset.confirm);"
                                  data-confirm="{{ __('¿Eliminar la oferta «:titulo»?', ['titulo' => $oferta->titulo]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Eliminar') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('Aún no has creado ofertas.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $ofertas->links() }}</div>
@endsection
