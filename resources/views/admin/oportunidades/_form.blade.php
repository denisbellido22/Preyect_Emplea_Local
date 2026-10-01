@php($oportunidad = $oportunidad ?? null)

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="titulo" class="form-label">Título *</label>
        <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $oportunidad->titulo ?? '') }}"
               class="form-control @error('titulo') is-invalid @enderror" required>
        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="tipo" class="form-label">Tipo *</label>
        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
            <option value="">Selecciona...</option>
            @foreach($tipos as $t)
                <option value="{{ $t }}" {{ old('tipo', $oportunidad->tipo ?? '') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label for="descripcion" class="form-label">Descripción *</label>
    <textarea name="descripcion" id="descripcion" rows="4"
              class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion', $oportunidad->descripcion ?? '') }}</textarea>
    @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="institucion" class="form-label">Institución</label>
        <input type="text" name="institucion" id="institucion" value="{{ old('institucion', $oportunidad->institucion ?? '') }}"
               class="form-control @error('institucion') is-invalid @enderror">
        @error('institucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="lugar" class="form-label">Lugar</label>
        <input type="text" name="lugar" id="lugar" value="{{ old('lugar', $oportunidad->lugar ?? '') }}"
               class="form-control @error('lugar') is-invalid @enderror">
        @error('lugar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="fecha" class="form-label">Fecha</label>
        <input type="date" name="fecha" id="fecha"
               value="{{ old('fecha', isset($oportunidad->fecha) ? $oportunidad->fecha?->format('Y-m-d') : '') }}"
               class="form-control @error('fecha') is-invalid @enderror">
        @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8 mb-3">
        <label for="enlace" class="form-label">Enlace</label>
        <input type="url" name="enlace" id="enlace" value="{{ old('enlace', $oportunidad->enlace ?? '') }}"
               placeholder="https://..." class="form-control @error('enlace') is-invalid @enderror">
        @error('enlace')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="imagen" class="form-label">Imagen (JPG, JPEG, PNG, WEBP · máx. 2 MB)</label>
        <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.webp"
               class="form-control @error('imagen') is-invalid @enderror">
        @error('imagen')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if(!empty($oportunidad?->imagen))
            <div class="mt-2">
                <img src="{{ asset($oportunidad->imagen) }}" alt="Imagen actual" class="rounded object-cover" style="height: 90px;">
                <div class="form-text">Imagen actual. Sube otra para reemplazarla.</div>
            </div>
        @endif
    </div>
    <div class="col-md-6 mb-3">
        <label for="estado" class="form-label">Estado *</label>
        <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
            <option value="publicado" {{ old('estado', $oportunidad->estado ?? 'publicado') === 'publicado' ? 'selected' : '' }}>Publicado</option>
            <option value="borrador" {{ old('estado', $oportunidad->estado ?? '') === 'borrador' ? 'selected' : '' }}>Borrador</option>
        </select>
        @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
