@extends('layouts.app')

@section('title', 'Editar oferta')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class="bi bi-pencil-square text-brand me-1"></i>Editar oferta (administrador)</h4>

                    <form action="{{ route('admin.ofertas.update', $oferta) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título *</label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $oferta->titulo) }}"
                                   class="form-control @error('titulo') is-invalid @enderror" required>
                            @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea name="descripcion" id="descripcion" rows="4"
                                      class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion', $oferta->descripcion) }}</textarea>
                            @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="requisitos" class="form-label">Requisitos *</label>
                            <textarea name="requisitos" id="requisitos" rows="4"
                                      class="form-control @error('requisitos') is-invalid @enderror" required>{{ old('requisitos', $oferta->requisitos) }}</textarea>
                            @error('requisitos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lugar" class="form-label">Lugar *</label>
                                <input type="text" name="lugar" id="lugar" value="{{ old('lugar', $oferta->lugar) }}"
                                       class="form-control @error('lugar') is-invalid @enderror" required>
                                @error('lugar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_trabajo" class="form-label">Tipo de trabajo *</label>
                                <input type="text" name="tipo_trabajo" id="tipo_trabajo" value="{{ old('tipo_trabajo', $oferta->tipo_trabajo) }}"
                                       class="form-control @error('tipo_trabajo') is-invalid @enderror" required>
                                @error('tipo_trabajo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="salario" class="form-label">Salario (S/)</label>
                                <input type="number" step="0.01" min="0" name="salario" id="salario"
                                       value="{{ old('salario', $oferta->salario) }}"
                                       class="form-control @error('salario') is-invalid @enderror">
                                @error('salario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_limite" class="form-label">Fecha límite *</label>
                                <input type="date" name="fecha_limite" id="fecha_limite"
                                       value="{{ old('fecha_limite', $oferta->fecha_limite?->format('Y-m-d')) }}"
                                       class="form-control @error('fecha_limite') is-invalid @enderror" required>
                                @error('fecha_limite')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                @foreach(['pendiente', 'aprobado', 'rechazado'] as $estado)
                                    <option value="{{ $estado }}" {{ old('estado', $oferta->estado) === $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                                @endforeach
                            </select>
                            @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-brand">Guardar cambios</button>
                            <a href="{{ route('admin.ofertas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
