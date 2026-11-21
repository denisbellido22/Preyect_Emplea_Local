@extends('layouts.app')

@section('title', __('Editar usuario'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class="bi bi-pencil-square text-brand me-1"></i>{{ __('Editar usuario') }}</h4>

                    <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nombre para mostrar *') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Correo electrónico *') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">{{ __('Rol *') }}</label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                    @foreach(['admin', 'empleador', 'postulante'] as $value)
                                        <option value="{{ $value }}" {{ old('role', $usuario->role) === $value ? 'selected' : '' }}>{{ __('role.'.$value) }}</option>
                                    @endforeach
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">{{ __('Teléfono') }}</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                                       class="form-control @error('telefono') is-invalid @enderror">
                                @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="distrito" class="form-label">{{ __('Distrito') }}</label>
                            <input type="text" name="distrito" id="distrito" value="{{ old('distrito', $usuario->distrito) }}"
                                   class="form-control @error('distrito') is-invalid @enderror">
                            @error('distrito')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-brand">{{ __('Guardar cambios') }}</button>
                            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
