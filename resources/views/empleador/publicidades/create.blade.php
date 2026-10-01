@extends('layouts.app')

@section('title', 'Solicitar publicidad')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-1"><i class="bi bi-megaphone text-brand me-1"></i>Solicitar publicidad</h4>
                    <p class="text-muted small mb-4">La solicitud se registrará con estado <strong>PENDIENTE</strong> hasta la aprobación del administrador. No se realiza ningún pago en línea.</p>

                    <form action="{{ route('empleador.publicidades.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título *</label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}"
                                   class="form-control @error('titulo') is-invalid @enderror" required>
                            @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea name="descripcion" id="descripcion" rows="4"
                                      class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion') }}</textarea>
                            @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen * (JPG, JPEG, PNG, WEBP · máx. 2 MB)</label>
                            <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.webp"
                                   class="form-control @error('imagen') is-invalid @enderror" required>
                            @error('imagen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de inicio *</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}"
                                       class="form-control @error('fecha_inicio') is-invalid @enderror" required>
                                @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="form-label">Fecha de fin *</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}"
                                       class="form-control @error('fecha_fin') is-invalid @enderror" required>
                                @error('fecha_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-brand">Enviar solicitud</button>
                            <a href="{{ route('empleador.publicidades.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
