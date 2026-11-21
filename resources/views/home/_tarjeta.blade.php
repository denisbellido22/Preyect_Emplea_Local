@php
    $iconos = [
        'empleo' => 'bi-briefcase',
        'beca' => 'bi-mortarboard',
        'convocatoria' => 'bi-megaphone',
        'examen' => 'bi-clipboard-check',
        'practica' => 'bi-person-workspace',
        'capacitacion' => 'bi-easel',
        'curso' => 'bi-journal-bookmark',
        'charla' => 'bi-chat-square-text',
        'evento' => 'bi-calendar-event',
        'otra' => 'bi-stars',
    ];
@endphp

<article class="opportunity-card" data-key="{{ $publicacion['key'] }}" data-categoria="{{ $publicacion['categoria'] }}" data-orden="{{ $publicacion['orden'] }}" @if($oculta ?? false) hidden @endif>
    <div class="opportunity-media {{ $publicacion['imagen'] ? '' : 'is-placeholder' }}">
        @if($publicacion['imagen'])
            <img src="{{ $publicacion['imagen'] }}" alt="{{ $publicacion['titulo'] }}" loading="lazy">
        @else
            <i class="bi {{ $iconos[$publicacion['categoria']] ?? 'bi-stars' }}" aria-hidden="true"></i>
        @endif
    </div>

    <div class="opportunity-body">
        <div class="opportunity-tags">
            <span class="opportunity-tag cat-{{ $publicacion['categoria'] }}">{{ $publicacion['categoria_label'] }}</span>
            <span class="opportunity-new" hidden>{{ __('NUEVO') }}</span>
        </div>

        <h3 class="opportunity-title">
            <a href="{{ $publicacion['url'] }}">{{ $publicacion['titulo'] }}</a>
        </h3>

        <p class="opportunity-institution">
            <i class="bi bi-building" aria-hidden="true"></i>
            {{ $publicacion['institucion'] ?: __('Emplea Local') }}
        </p>

        <p class="opportunity-description">{{ $publicacion['descripcion'] }}</p>

        <ul class="opportunity-meta">
            <li><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $publicacion['lugar'] ?: __('Parinacochas') }}</li>

            @if($publicacion['tipo_trabajo'])
                <li><i class="bi bi-clock" aria-hidden="true"></i>{{ $publicacion['tipo_trabajo'] }}</li>
            @endif

            @if($publicacion['publicado_legible'])
                <li title="{{ $publicacion['publicado_relativo'] }}">
                    <i class="bi bi-calendar-plus" aria-hidden="true"></i>{{ $publicacion['publicado_legible'] }}
                </li>
            @endif

            @if($publicacion['fecha_limite_legible'])
                <li><i class="bi bi-calendar-event" aria-hidden="true"></i>{{ __('Hasta') }} {{ $publicacion['fecha_limite_legible'] }}</li>
            @endif

            <li><i class="bi bi-check2-circle" aria-hidden="true"></i>{{ $publicacion['estado_label'] }}</li>
        </ul>

        <div class="opportunity-footer">
            @if($publicacion['salario'])
                <span class="opportunity-salary">{{ $publicacion['salario'] }}</span>
            @else
                <span class="opportunity-state">{{ __('status.'.$publicacion['estado']) }}</span>
            @endif

            @php($puedePostular = $publicacion['tipo_publicacion'] === 'oferta' && auth()->check() && auth()->user()->isPostulante())
            <a href="{{ $puedePostular ? $publicacion['url'].'#postular' : $publicacion['url'] }}" class="btn btn-brand btn-sm">
                {{ $puedePostular ? __('Postularme') : __('Ver detalles') }} <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
