@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    @php
        $carrusel = [
            ['src' => 'assets/carrusel/equipo-trabajando.jpg', 'alt' => 'Equipo de trabajo colaborando', 'titulo' => 'Tu próximo empleo te espera', 'texto' => 'Encuentra ofertas laborales aprobadas en Parinacochas.'],
            ['src' => 'assets/carrusel/construccion.jpg', 'alt' => 'Trabajadores del sector construcción', 'titulo' => 'Oportunidades en todos los sectores', 'texto' => 'Construcción, comercio, salud, educación y más.'],
            ['src' => 'assets/carrusel/docentes.jpg', 'alt' => 'Docentes en el aula', 'titulo' => 'Becas, cursos y capacitaciones', 'texto' => 'Capacítate y mejora tu perfil profesional.'],
            ['src' => 'assets/carrusel/comida.png', 'alt' => 'Gastronomía y servicio', 'titulo' => 'Trabaja cerca de casa', 'texto' => 'Conectamos talento local con las empresas de la zona.'],
            ['src' => 'assets/carrusel/deliveri.jpg', 'alt' => 'Servicio de delivery', 'titulo' => 'Empleos con flexibilidad', 'texto' => 'Encuentra trabajos a tiempo completo o parcial.'],
            ['src' => 'assets/carrusel/tiendaropa.jpg', 'alt' => 'Tienda de ropa', 'titulo' => 'Impulsa tu negocio', 'texto' => 'Publica tus ofertas y llega a más postulantes.'],
        ];
    @endphp

    <div id="inicioCarousel" class="carousel slide home-carousel mb-4" data-bs-ride="carousel" data-bs-interval="4500">
        <div class="carousel-indicators">
            @foreach($carrusel as $i => $img)
                <button type="button" data-bs-target="#inicioCarousel" data-bs-slide-to="{{ $i }}"
                        class="{{ $i === 0 ? 'active' : '' }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                        aria-label="Imagen {{ $i + 1 }}"></button>
            @endforeach
        </div>
        <div class="carousel-inner">
            @foreach($carrusel as $i => $img)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    <img src="{{ asset($img['src']) }}" alt="{{ $img['alt'] }}">
                    <div class="carousel-caption d-none d-md-block">
                        <h5 class="fw-bold mb-1">{{ $img['titulo'] }}</h5>
                        <p class="mb-0 small">{{ $img['texto'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#inicioCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#inicioCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>

    <section class="hero p-4 p-md-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-5 fw-bold mb-2">EMPLEA LOCAL</h1>
                <p class="lead mb-4">Conectando oportunidades laborales en Parinacochas</p>
                <form action="{{ route('ofertas.index') }}" method="GET" class="row g-2">
                    <div class="col-sm-8">
                        <input type="text" name="q" class="form-control form-control-lg"
                               placeholder="Buscar por título, ubicación o tipo de trabajo...">
                    </div>
                    <div class="col-sm-4 d-grid">
                        <button class="btn btn-warning btn-lg fw-semibold" type="submit">
                            <i class="bi bi-search me-1"></i>Buscar ofertas
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <img src="{{ asset('assets/logo/logo.png') }}" alt="EMPLEA LOCAL" class="hero-logo logo-on-dark mx-auto">
            </div>
        </div>
    </section>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-briefcase text-brand me-1"></i>Ofertas recientes</h4>
        <a href="{{ route('ofertas.index') }}" class="btn btn-sm btn-outline-brand">Ver todas</a>
    </div>

    @if($ofertas->isEmpty())
        <div class="alert alert-info">Aún no hay ofertas aprobadas publicadas.</div>
    @else
        <div class="row g-3 mb-5">
            @foreach($ofertas as $oferta)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $oferta->titulo }}</h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt"></i> {{ $oferta->lugar }}
                                &nbsp;·&nbsp; <i class="bi bi-clock"></i> {{ $oferta->tipo_trabajo }}
                            </p>
                            <p class="card-text flex-grow-1">{{ \Illuminate\Support\Str::limit($oferta->descripcion, 110) }}</p>
                            @if($oferta->salario)
                                <p class="fw-semibold text-brand mb-2">S/ {{ number_format($oferta->salario, 2) }}</p>
                            @endif
                            <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-brand btn-sm align-self-start">Ver detalle</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($oportunidades->isNotEmpty())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bi bi-mortarboard text-brand me-1"></i>Oportunidades</h4>
            @auth
                <a href="{{ route('oportunidades.index') }}" class="btn btn-sm btn-outline-brand">Ver todas</a>
            @endauth
        </div>
        <div class="row g-3 mb-5">
            @foreach($oportunidades as $oportunidad)
                <div class="col-md-4">
                    <div class="card h-100">
                        @if($oportunidad->imagen)
                            <img src="{{ asset($oportunidad->imagen) }}" class="card-img-top object-cover" style="height: 150px;" alt="{{ $oportunidad->titulo }}">
                        @endif
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2">{{ ucfirst($oportunidad->tipo) }}</span>
                            <h6 class="card-title">{{ $oportunidad->titulo }}</h6>
                            <p class="text-muted small mb-0">{{ $oportunidad->institucion }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($publicidades->isNotEmpty())
        <h4 class="mb-3"><i class="bi bi-megaphone text-brand me-1"></i>Publicidad</h4>
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
    @endif
@endsection
