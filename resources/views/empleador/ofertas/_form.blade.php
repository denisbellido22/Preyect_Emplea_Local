@php($oferta = $oferta ?? null)

<div class="mb-3">
    <label for="titulo" class="form-label">{{ __('Título *') }}</label>
    <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $oferta->titulo ?? '') }}"
           class="form-control @error('titulo') is-invalid @enderror" required>
    @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="descripcion" class="form-label">{{ __('Descripción *') }}</label>
    <textarea name="descripcion" id="descripcion" rows="4"
              class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion', $oferta->descripcion ?? '') }}</textarea>
    @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="requisitos" class="form-label">{{ __('Requisitos *') }}</label>
    <textarea name="requisitos" id="requisitos" rows="4"
              class="form-control @error('requisitos') is-invalid @enderror" required>{{ old('requisitos', $oferta->requisitos ?? '') }}</textarea>
    @error('requisitos')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="lugar" class="form-label">{{ __('Lugar *') }}</label>
        <input type="text" name="lugar" id="lugar" value="{{ old('lugar', $oferta->lugar ?? '') }}"
               class="form-control @error('lugar') is-invalid @enderror" required>
        @error('lugar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="tipo_trabajo" class="form-label">{{ __('Tipo de trabajo *') }}</label>
        <select name="tipo_trabajo" id="tipo_trabajo" class="form-select @error('tipo_trabajo') is-invalid @enderror" required>
            <option value="">{{ __('Selecciona...') }}</option>
            @foreach(['Tiempo completo', 'Medio tiempo', 'Por horas', 'Temporal', 'Pasantía', 'Freelance'] as $tipo)
                <option value="{{ $tipo }}" {{ old('tipo_trabajo', $oferta->tipo_trabajo ?? '') === $tipo ? 'selected' : '' }}>{{ __('job_type.'.$tipo) }}</option>
            @endforeach
        </select>
        @error('tipo_trabajo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="salario" class="form-label">{{ __('Salario (S/)') }}</label>
        <input type="number" step="0.01" min="0" name="salario" id="salario"
               value="{{ old('salario', $oferta->salario ?? '') }}"
               class="form-control @error('salario') is-invalid @enderror">
        @error('salario')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="fecha_limite" class="form-label">{{ __('Fecha límite *') }}</label>
        <input type="date" name="fecha_limite" id="fecha_limite"
               value="{{ old('fecha_limite', isset($oferta->fecha_limite) ? $oferta->fecha_limite->format('Y-m-d') : '') }}"
               class="form-control @error('fecha_limite') is-invalid @enderror" required>
        @error('fecha_limite')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
