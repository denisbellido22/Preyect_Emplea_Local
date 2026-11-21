@extends('layouts.app')

@section('title', __('Editar oferta'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-pencil-square text-brand me-1"></i>{{ __('Editar oferta') }}</h4>
                    <p class="text-muted small mb-4">{{ __('Al guardar cambios, la oferta volverá al estado') }} <strong>{{ __('status.pendiente') }}</strong> {{ __('para su revisión.') }}</p>

                    <form action="{{ route('empleador.ofertas.update', $oferta) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('empleador.ofertas._form', ['oferta' => $oferta])

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-brand">{{ __('Guardar cambios') }}</button>
                            <a href="{{ route('empleador.ofertas.index') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
