@extends('layouts.app')

@section('title', __('Mis postulaciones'))

@section('content')
    <h4 class="mb-3"><i class="bi bi-file-earmark-text text-brand me-1"></i>{{ __('Mis postulaciones') }}</h4>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Oferta') }}</th>
                        <th>{{ __('Empleador') }}</th>
                        <th>{{ __('Fecha de postulación') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($postulaciones as $p)
                    <tr>
                        <td>{{ $p->oferta?->titulo }}</td>
                        <td class="small">{{ $p->oferta?->empleador?->nombreCompleto() }}</td>
                        <td class="small">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge badge-soft-{{ $p->estado }}">{{ __('status.'.$p->estado) }}</span></td>
                        <td class="text-end">
                            @if($p->oferta)
                                <a href="{{ route('ofertas.show', $p->oferta) }}" class="btn btn-sm btn-outline-brand">{{ __('Ver oferta') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">
                        {{ __('Aún no te has postulado a ninguna oferta.') }} <a href="{{ route('ofertas.index') }}">{{ __('Buscar ofertas') }}</a>.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $postulaciones->links() }}</div>
@endsection
