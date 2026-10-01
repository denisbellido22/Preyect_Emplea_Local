@extends('layouts.app')

@section('title', 'Editar perfil')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class="bi bi-pencil-square text-brand me-1"></i>Editar perfil</h4>

                    <form action="{{ route('perfil.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombres" class="form-label">Nombres *</label>
                                <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $user->nombres) }}"
                                       class="form-control @error('nombres') is-invalid @enderror" required>
                                @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label">Apellidos *</label>
                                <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos', $user->apellidos) }}"
                                       class="form-control @error('apellidos') is-invalid @enderror" required>
                                @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre para mostrar *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Correo electrónico *</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $user->telefono) }}"
                                       class="form-control @error('telefono') is-invalid @enderror">
                                @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="distrito" class="form-label">Distrito</label>
                                <input type="text" name="distrito" id="distrito" value="{{ old('distrito', $user->distrito) }}"
                                       class="form-control @error('distrito') is-invalid @enderror">
                                @error('distrito')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            @if($user->isPostulante())
                                <div class="col-md-6 mb-3">
                                    <label for="dni" class="form-label">DNI (8 dígitos) *</label>
                                    <input type="text" name="dni" id="dni" value="{{ old('dni', $user->dni) }}"
                                           class="form-control @error('dni') is-invalid @enderror" inputmode="numeric">
                                    @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @else
                                <div class="col-md-6 mb-3">
                                    <label for="empresa" class="form-label">Empresa / institución</label>
                                    <input type="text" name="empresa" id="empresa" value="{{ old('empresa', $user->empresa) }}"
                                           class="form-control @error('empresa') is-invalid @enderror">
                                    @error('empresa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                        </div>

                        @if($user->isPostulante())
                            <div class="mb-3">
                                <label for="profesion" class="form-label">Profesión u ocupación *</label>
                                <input type="text" name="profesion" id="profesion" value="{{ old('profesion', $user->profesion) }}"
                                       class="form-control @error('profesion') is-invalid @enderror">
                                @error('profesion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label for="experiencia" class="form-label">Experiencia</label>
                                <textarea name="experiencia" id="experiencia" rows="4"
                                          class="form-control @error('experiencia') is-invalid @enderror">{{ old('experiencia', $user->experiencia) }}</textarea>
                                @error('experiencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3"
                                      class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $user->descripcion) }}</textarea>
                            @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-brand">Guardar cambios</button>
                            <a href="{{ route('perfil.show') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
