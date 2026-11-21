@extends('layouts.app')

@section('title', __('Inicio'))

@section('content')
    @php
        $carrusel = [
            ['src' => 'assets/carrusel/equipo-trabajando.jpg', 'alt' => __('Equipo de trabajo colaborando'), 'titulo' => __('Tu próximo empleo te espera'), 'texto' => __('Ofertas laborales aprobadas en Parinacochas.')],
            ['src' => 'assets/carrusel/construccion.jpg', 'alt' => __('Trabajadores del sector construcción'), 'titulo' => __('Oportunidades en todos los sectores'), 'texto' => __('Construcción, comercio, salud, educación y más.')],
            ['src' => 'assets/carrusel/docentes.jpg', 'alt' => __('Docentes en el aula'), 'titulo' => __('Becas, cursos y capacitaciones'), 'texto' => __('Capacítate y mejora tu perfil profesional.')],
            ['src' => 'assets/carrusel/comida.png', 'alt' => __('Gastronomía y servicio'), 'titulo' => __('Trabaja cerca de casa'), 'texto' => __('Conectamos talento local con las empresas de la zona.')],
            ['src' => 'assets/carrusel/deliveri.jpg', 'alt' => __('Servicio de delivery'), 'titulo' => __('Empleos con flexibilidad'), 'texto' => __('Encuentra trabajos a tiempo completo o parcial.')],
            ['src' => 'assets/carrusel/tiendaropa.jpg', 'alt' => __('Tienda de ropa'), 'titulo' => __('Impulsa tu negocio'), 'texto' => __('Publica tus ofertas y llega a más postulantes.')],
        ];

        $iconosCategoria = [
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

        // Categorías realmente presentes en el feed, en el orden de los filtros.
        $categoriasDisponibles = collect(array_keys(\App\Support\PublicacionPublica::CATEGORIAS))
            ->filter(fn ($categoria) => collect($ultimasPublicaciones)->contains('categoria', $categoria))
            ->values()
            ->all();

        $destacadosAcademicos = collect($ultimasPublicaciones)
            ->whereIn('categoria', ['beca', 'convocatoria', 'examen', 'practica', 'capacitacion', 'curso'])
            ->take(3)
            ->values();

        $totalPublicaciones = count($ultimasPublicaciones);
    @endphp

    {{-- ===================== HERO ===================== --}}
    <section class="hero-section">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <span class="hero-eyebrow">
                    <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                    {{ __('Parinacochas · Ayacucho') }}
                </span>

                <h1 class="hero-title">
                    {{ __('Tu próximo empleo o beca está más cerca de lo que crees') }}
                </h1>

                <p class="hero-subtitle">
                    {{ __('Emplea Local conecta el talento de Parinacochas con empresas, instituciones y programas de formación. Encuentra empleos verificados, becas, convocatorias y capacitaciones en un solo lugar.') }}
                </p>

                <form action="{{ route('ofertas.index') }}" method="GET" class="hero-search" role="search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <label for="hero-busqueda" class="visually-hidden">{{ __('Buscar oportunidades') }}</label>
                    <input type="text" id="hero-busqueda" name="q" class="hero-search-input"
                           placeholder="{{ __('Buscar por puesto, empresa o distrito…') }}">
                    <button type="submit" class="btn btn-hero-search">
                        {{ __('Buscar') }}
                    </button>
                </form>

                <div class="hero-actions">
                    <a href="{{ route('ofertas.index') }}" class="btn btn-hero-primary">
                        <i class="bi bi-briefcase" aria-hidden="true"></i> {{ __('Explorar empleos') }}
                    </a>
                    <a href="#ultimas-oportunidades" class="btn btn-hero-ghost">
                        <i class="bi bi-mortarboard" aria-hidden="true"></i> {{ __('Ver becas y oportunidades') }}
                    </a>
                </div>

                <div class="hero-trust">
                    <span><i class="bi bi-shield-check" aria-hidden="true"></i> {{ __('Publicaciones verificadas') }}</span>
                    <span><i class="bi bi-lightning-charge" aria-hidden="true"></i> {{ __('Actualización en tiempo real') }}</span>
                </div>
            </div>

            <div class="col-lg-6">
                <div id="heroCarousel" class="carousel slide carousel-fade hero-carousel" data-bs-ride="carousel" data-bs-interval="4500">
                    <div class="carousel-indicators">
                        @foreach($carrusel as $i => $img)
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $i }}"
                                    class="{{ $i === 0 ? 'active' : '' }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                                    aria-label="{{ __('Imagen :numero', ['numero' => $i + 1]) }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner">
                        @foreach($carrusel as $i => $img)
                            <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                <img src="{{ asset($img['src']) }}" alt="{{ $img['alt'] }}">
                                <div class="carousel-caption d-none d-sm-block">
                                    <strong>{{ $img['titulo'] }}</strong>
                                    <span>{{ $img['texto'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">{{ __('Anterior') }}</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">{{ __('Siguiente') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== ESTADÍSTICAS ===================== --}}
    <section class="stats-strip" aria-label="{{ __('Emplea Local en cifras') }}">
        <div class="stat-item">
            <span class="stat-value">{{ number_format($estadisticas['empleos']) }}</span>
            <span class="stat-label">{{ __('Empleos activos') }}</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">{{ number_format($estadisticas['oportunidades']) }}</span>
            <span class="stat-label">{{ __('Becas y oportunidades') }}</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">{{ number_format($estadisticas['instituciones']) }}</span>
            <span class="stat-label">{{ __('Instituciones participantes') }}</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">{{ number_format($estadisticas['distritos']) }}</span>
            <span class="stat-label">{{ __('Distritos con ofertas') }}</span>
        </div>
    </section>

    {{-- ===================== ÚLTIMAS OPORTUNIDADES (TIEMPO REAL) ===================== --}}
    <section id="ultimas-oportunidades" class="oportunidades-section" aria-labelledby="titulo-oportunidades">
        <div class="section-heading">
            <div>
                <span class="section-kicker">{{ __('Actualizado en tiempo real') }}</span>
                <h2 id="titulo-oportunidades" class="section-title">
                    {{ __('Últimas oportunidades laborales y académicas') }}
                </h2>
                <p class="section-subtitle">
                    {{ __('Empleos, becas, convocatorias, exámenes, prácticas y capacitaciones publicadas recientemente.') }}
                </p>
            </div>
            <div class="section-heading-side">
                <span id="realtime-indicador" class="live-indicator" hidden>
                    <span class="live-dot" aria-hidden="true"></span>
                    <span class="live-text">{{ __('En vivo') }}</span>
                </span>
                <span class="section-count"><span id="oportunidades-contador">{{ $totalPublicaciones }}</span> {{ __('publicaciones') }}</span>
            </div>
        </div>

        <div id="realtime-aviso" class="realtime-alert" role="status" hidden></div>

        @if($categoriasDisponibles !== [])
            <div class="opportunity-filters" role="group" aria-label="{{ __('Filtrar por categoría') }}">
                <button type="button" class="filter-chip is-active" data-filtro="todas" aria-pressed="true">
                    {{ __('Todas') }}
                </button>
                @foreach($categoriasDisponibles as $categoria)
                    <button type="button" class="filter-chip" data-filtro="{{ $categoria }}" aria-pressed="false">
                        <i class="bi {{ $iconosCategoria[$categoria] ?? 'bi-stars' }}" aria-hidden="true"></i>
                        {{ __('opportunity_type.'.$categoria) }}
                    </button>
                @endforeach
            </div>
        @endif

        <div id="oportunidades-grid" class="opportunity-grid" data-visible-limit="6">
            @foreach($ultimasPublicaciones as $i => $publicacion)
                @include('home._tarjeta', ['publicacion' => $publicacion, 'oculta' => $i >= 6])
            @endforeach
        </div>

        <div id="oportunidades-vacio" class="empty-state" @if($totalPublicaciones > 0) hidden @endif>
            <i class="bi bi-inbox" aria-hidden="true"></i>
            <h3>{{ __('Todavía no hay oportunidades publicadas') }}</h3>
            <p>{{ __('Vuelve pronto: las nuevas publicaciones aparecerán aquí automáticamente.') }}</p>
        </div>

        <div class="oportunidades-acciones">
            <button type="button" id="ver-todas-oportunidades" class="btn btn-outline-brand"
                    aria-expanded="false" @if($totalPublicaciones <= 6) hidden @endif>
                {{ __('Ver todas las oportunidades') }}
            </button>
            <a href="{{ route('ofertas.index') }}" class="btn btn-outline-brand">
                {{ __('Explorar empleos') }}
            </a>
            <a href="{{ route('oportunidades.index') }}" class="btn btn-outline-brand">
                {{ __('Explorar becas y convocatorias') }}
            </a>
        </div>

        <div id="realtime-notificaciones" class="realtime-notifications" aria-live="polite" aria-atomic="false"></div>

        {{-- Plantilla que JavaScript clona para insertar publicaciones en tiempo real --}}
        <template id="plantilla-oportunidad">
            <article class="opportunity-card" data-key="" data-categoria="" data-orden="">
                <div class="opportunity-media" data-campo="media"></div>
                <div class="opportunity-body">
                    <div class="opportunity-tags">
                        <span class="opportunity-tag" data-campo="categoria"></span>
                        <span class="opportunity-new" hidden>{{ __('NUEVO') }}</span>
                    </div>
                    <h3 class="opportunity-title">
                        <a data-campo="titulo-enlace" href="#"><span data-campo="titulo"></span></a>
                    </h3>
                    <p class="opportunity-institution">
                        <i class="bi bi-building" aria-hidden="true"></i>
                        <span data-campo="institucion"></span>
                    </p>
                    <p class="opportunity-description" data-campo="descripcion"></p>
                    <ul class="opportunity-meta">
                        <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span data-campo="lugar"></span></li>
                        <li data-campo="tipo-trabajo" hidden></li>
                        <li><i class="bi bi-calendar-plus" aria-hidden="true"></i><span data-campo="publicado"></span></li>
                        <li data-campo="limite-wrap" hidden>
                            <i class="bi bi-calendar-event" aria-hidden="true"></i>
                            <span data-campo="limite"></span>
                        </li>
                        <li><i class="bi bi-check2-circle" aria-hidden="true"></i><span data-campo="estado"></span></li>
                    </ul>
                    <div class="opportunity-footer">
                        <span data-campo="salario" hidden></span>
                        <a data-campo="url" href="#" class="btn btn-brand btn-sm">
                            <span data-campo="accion">{{ __('Ver detalles') }}</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </article>
        </template>
    </section>

    {{-- ===================== CATEGORÍAS ===================== --}}
    <section class="home-section" aria-labelledby="titulo-categorias">
        <div class="section-heading">
            <div>
                <span class="section-kicker">{{ __('Explora por categoría') }}</span>
                <h2 id="titulo-categorias" class="section-title">{{ __('Categorías de oportunidades') }}</h2>
            </div>
        </div>

        @php
            $categoriasHome = [
                ['clave' => 'empleo', 'titulo' => __('Empleos'), 'texto' => __('Ofertas laborales aprobadas'), 'ruta' => route('ofertas.index'), 'icono' => 'bi-briefcase'],
                ['clave' => 'beca', 'titulo' => __('Becas'), 'texto' => __('Apoyos para tus estudios'), 'ruta' => route('oportunidades.index', ['tipo' => 'beca']), 'icono' => 'bi-mortarboard'],
                ['clave' => 'convocatoria', 'titulo' => __('Convocatorias'), 'texto' => __('Procesos de selección abiertos'), 'ruta' => route('oportunidades.index', ['tipo' => 'convocatoria']), 'icono' => 'bi-megaphone'],
                ['clave' => 'examen', 'titulo' => __('Exámenes'), 'texto' => __('Evaluaciones y resultados'), 'ruta' => route('oportunidades.index', ['tipo' => 'examen']), 'icono' => 'bi-clipboard-check'],
                ['clave' => 'practica', 'titulo' => __('Prácticas'), 'texto' => __('Experiencia profesional inicial'), 'ruta' => route('oportunidades.index', ['tipo' => 'practica']), 'icono' => 'bi-person-workspace'],
                ['clave' => 'capacitacion', 'titulo' => __('Capacitaciones'), 'texto' => __('Cursos y formación gratuita'), 'ruta' => route('oportunidades.index', ['tipo' => 'capacitacion']), 'icono' => 'bi-easel'],
            ];
        @endphp

        <div class="category-grid">
            @foreach($categoriasHome as $categoria)
                <a href="{{ $categoria['ruta'] }}" class="category-card cat-{{ $categoria['clave'] }}">
                    <span class="category-icon"><i class="bi {{ $categoria['icono'] }}" aria-hidden="true"></i></span>
                    <span class="category-name">{{ $categoria['titulo'] }}</span>
                    <span class="category-text">{{ $categoria['texto'] }}</span>
                    <span class="category-arrow"><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ===================== DESTACADOS ACADÉMICOS ===================== --}}
    @if($destacadosAcademicos->isNotEmpty())
        <section class="home-section" aria-labelledby="titulo-academicos">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">{{ __('Formación y convocatorias') }}</span>
                    <h2 id="titulo-academicos" class="section-title">{{ __('Becas y convocatorias destacadas') }}</h2>
                </div>
                <a href="{{ route('oportunidades.index') }}" class="btn btn-sm btn-outline-brand">{{ __('Ver todas') }}</a>
            </div>

            <div class="featured-list">
                @foreach($destacadosAcademicos as $destacado)
                    <a href="{{ $destacado['url'] }}" class="featured-item">
                        <span class="featured-icon cat-{{ $destacado['categoria'] }}">
                            <i class="bi {{ $iconosCategoria[$destacado['categoria']] ?? 'bi-stars' }}" aria-hidden="true"></i>
                        </span>
                        <span class="featured-content">
                            <span class="featured-tag">{{ $destacado['categoria_label'] }}</span>
                            <strong class="featured-title">{{ $destacado['titulo'] }}</strong>
                            <span class="featured-meta">
                                {{ $destacado['institucion'] ?: __('Emplea Local') }}
                                @if($destacado['fecha_limite_legible'])
                                    · {{ __('Hasta') }} {{ $destacado['fecha_limite_legible'] }}
                                @endif
                            </span>
                        </span>
                        <i class="bi bi-chevron-right featured-arrow" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===================== INSTITUCIONES ===================== --}}
    @if($instituciones !== [])
        <section class="home-section" aria-labelledby="titulo-instituciones">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">{{ __('Red local') }}</span>
                    <h2 id="titulo-instituciones" class="section-title">{{ __('Empresas e instituciones participantes') }}</h2>
                </div>
            </div>

            <div class="institution-grid">
                @foreach($instituciones as $institucion)
                    <div class="institution-chip">
                        <span class="institution-initial">{{ mb_strtoupper(mb_substr($institucion, 0, 1)) }}</span>
                        <span class="institution-name">{{ $institucion }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===================== BENEFICIOS ===================== --}}
    <section id="sobre-la-plataforma" class="home-section" aria-labelledby="titulo-beneficios">
        <div class="section-heading">
            <div>
                <span class="section-kicker">{{ __('Sobre la plataforma') }}</span>
                <h2 id="titulo-beneficios" class="section-title">{{ __('¿Por qué usar Emplea Local?') }}</h2>
            </div>
        </div>

        <div class="benefit-grid">
            <div class="benefit-card">
                <span class="benefit-icon"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
                <h3>{{ __('Publicaciones verificadas') }}</h3>
                <p>{{ __('Cada oferta pasa por moderación antes de publicarse, para que postules con confianza.') }}</p>
            </div>
            <div class="benefit-card">
                <span class="benefit-icon"><i class="bi bi-broadcast" aria-hidden="true"></i></span>
                <h3>{{ __('Oportunidades al instante') }}</h3>
                <p>{{ __('Las nuevas publicaciones aparecen en tu pantalla automáticamente, sin recargar la página.') }}</p>
            </div>
            <div class="benefit-card">
                <span class="benefit-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                <h3>{{ __('Enfoque local') }}</h3>
                <p>{{ __('Conectamos empresas, instituciones y talento de Parinacochas y provincias cercanas.') }}</p>
            </div>
        </div>
    </section>

    {{-- ===================== PUBLICIDAD ===================== --}}
    @if($publicidades->isNotEmpty())
        <section class="home-section" aria-labelledby="titulo-publicidad">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">{{ __('Espacios aliados') }}</span>
                    <h2 id="titulo-publicidad" class="section-title">{{ __('Publicidad') }}</h2>
                </div>
            </div>

            <div class="row g-3">
                @foreach($publicidades as $publicidad)
                    <div class="col-md-4">
                        <div class="card h-100">
                            @if($publicidad->imagen)
                                <img src="{{ asset($publicidad->imagen) }}" class="card-img-top object-cover" style="height: 150px;" alt="{{ $publicidad->titulo }}">
                            @endif
                            <div class="card-body">
                                <h6 class="card-title">{{ $publicidad->titulo }}</h6>
                                <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($publicidad->descripcion, 90) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
