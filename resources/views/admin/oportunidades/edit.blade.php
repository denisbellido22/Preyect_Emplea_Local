@extends('layouts.app')

@section('title', __('Editar oportunidad'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class="bi bi-pencil-square text-brand me-1"></i>{{ __('Editar oportunidad') }}</h4>

                    <form action="{{ route('admin.oportunidades.update', $oportunidad) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('admin.oportunidades._form', ['oportunidad' => $oportunidad])

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-brand">{{ __('Guardar cambios') }}</button>
                            <a href="{{ route('admin.oportunidades.index') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
