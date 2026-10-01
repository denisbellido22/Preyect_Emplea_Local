@extends('layouts.app')

@section('title', 'Nueva oportunidad')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class="bi bi-plus-circle text-brand me-1"></i>Nueva oportunidad</h4>

                    <form action="{{ route('admin.oportunidades.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('admin.oportunidades._form', ['oportunidad' => null])

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-brand">Crear oportunidad</button>
                            <a href="{{ route('admin.oportunidades.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
