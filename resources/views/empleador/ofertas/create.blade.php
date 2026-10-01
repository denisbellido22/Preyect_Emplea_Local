@extends('layouts.app')

@section('title', 'Nueva oferta')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-plus-circle text-brand me-1"></i>Nueva oferta laboral</h4>
                    <p class="text-muted small mb-4">La oferta se guardará con estado <strong>PENDIENTE</strong> hasta que el administrador la apruebe.</p>

                    <form action="{{ route('empleador.ofertas.store') }}" method="POST">
                        @csrf
                        @include('empleador.ofertas._form', ['oferta' => null])

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-brand">Registrar oferta</button>
                            <a href="{{ route('empleador.ofertas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
