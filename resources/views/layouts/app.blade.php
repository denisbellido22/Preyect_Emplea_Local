<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') · EMPLEA LOCAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0f766e;
            --brand-dark: #115e59;
            --brand-accent: #f59e0b;
        }
        body { background-color: #f4f6f8; }
        .navbar-brand { font-weight: 700; letter-spacing: .5px; }
        .bg-brand { background-color: var(--brand) !important; }
        .text-brand { color: var(--brand) !important; }
        .btn-brand { background-color: var(--brand); border-color: var(--brand); color: #fff; }
        .btn-brand:hover { background-color: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }
        .btn-outline-brand { color: var(--brand); border-color: var(--brand); }
        .btn-outline-brand:hover { background-color: var(--brand); color: #fff; }
        .card { border: none; box-shadow: 0 1px 3px rgba(16, 24, 40, .08); }
        .hero {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            color: #fff;
            border-radius: .75rem;
        }
        .stat-card .stat-value { font-size: 1.9rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { color: #6b7280; font-size: .85rem; text-transform: uppercase; letter-spacing: .04em; }
        .badge-soft-pendiente { background: #fef3c7; color: #92400e; }
        .badge-soft-aprobado { background: #dcfce7; color: #166534; }
        .badge-soft-rechazado { background: #fee2e2; color: #991b1b; }
        .badge-soft-revisada { background: #dbeafe; color: #1e40af; }
        .badge-soft-aceptada { background: #dcfce7; color: #166534; }
        .badge-soft-finalizado { background: #e5e7eb; color: #374151; }
        .badge-soft-borrador { background: #e5e7eb; color: #374151; }
        .badge-soft-publicado { background: #dcfce7; color: #166534; }
        footer { color: #6b7280; }
        .object-cover { object-fit: cover; }

        /* Logo de la marca (el PNG ya viene sin fondo, no se le añade recuadro) */
        .navbar-logo { height: 64px; width: auto; display: block; }
        .hero-logo { height: 120px; width: auto; display: block; }
        /* Sobre fondos oscuros (footer/hero) el logo se muestra en blanco */
        .logo-on-dark { filter: brightness(0) invert(1); }

        /* Carrusel de portada */
        .home-carousel { border-radius: .75rem; overflow: hidden; box-shadow: 0 6px 20px rgba(16, 24, 40, .12); }
        .home-carousel .carousel-item img {
            width: 100%;
            height: clamp(220px, 40vw, 440px);
            object-fit: cover;
            display: block;
        }
        .home-carousel .carousel-caption {
            left: 0; right: 0; bottom: 0; text-align: left;
            background: linear-gradient(to top, rgba(15, 46, 43, .85), rgba(15, 46, 43, 0));
            padding: 2.5rem 1.5rem 1rem;
        }
        .home-carousel .carousel-indicators [data-bs-target] { width: 10px; height: 10px; border-radius: 50%; }

        /* Footer */
        .site-footer { background: #0f2e2b; color: #cbd5d3; margin-top: 3.5rem; }
        .site-footer .footer-logo { height: 52px; width: auto; display: block; }
        .social-link {
            display: inline-flex; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(255, 255, 255, .1);
            transition: transform .15s ease, background .15s ease;
        }
        .social-link:hover { background: rgba(255, 255, 255, .22); transform: translateY(-2px); }
        .social-link img { width: 22px; height: 22px; object-fit: contain; }

        @media (max-width: 575.98px) {
            .navbar-logo { height: 46px; }
            .social-link { width: 36px; height: 36px; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}" aria-label="EMPLEA LOCAL - Inicio">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="EMPLEA LOCAL" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('ofertas.index') }}">Ofertas</a></li>
                @auth
                    @if(auth()->user()->role !== 'admin')
                        <li class="nav-item"><a class="nav-link" href="{{ route('oportunidades.index') }}">Oportunidades</a></li>
                    @endif

                    @if(auth()->user()->role === 'empleador')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Empleador</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('empleador.ofertas.index') }}">Mis ofertas</a></li>
                                <li><a class="dropdown-item" href="{{ route('empleador.ofertas.create') }}">Nueva oferta</a></li>
                                <li><a class="dropdown-item" href="{{ route('empleador.postulantes.index') }}">Postulantes</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('empleador.publicidades.index') }}">Mi publicidad</a></li>
                                <li><a class="dropdown-item" href="{{ route('empleador.publicidades.create') }}">Solicitar publicidad</a></li>
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->role === 'postulante')
                        <li class="nav-item"><a class="nav-link" href="{{ route('postulaciones.index') }}">Mis postulaciones</a></li>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Administración</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.usuarios.index') }}">Usuarios</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.ofertas.index') }}">Moderar ofertas</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.oportunidades.index') }}">Oportunidades</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.publicidades.index') }}">Publicidad</a></li>
                            </ul>
                        </li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Ingresar</a></li>
                    <li class="nav-item"><a class="btn btn-brand btn-sm mt-1 ms-lg-2" href="{{ route('register') }}">Registrarse</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->nombreCompleto() }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small">{{ ucfirst(auth()->user()->role) }}</span></li>
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('perfil.show') }}">Mi perfil</a></li>
                            <li><a class="dropdown-item" href="{{ route('perfil.edit') }}">Editar perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong><i class="bi bi-x-circle me-1"></i>Revisa los siguientes errores:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="site-footer">
    <div class="container py-4">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('assets/logo/logo.png') }}" alt="EMPLEA LOCAL" class="footer-logo logo-on-dark">
                    <div>
                        <div class="fw-bold text-white">EMPLEA LOCAL</div>
                        <div class="small text-white-50">Conectando oportunidades laborales en Parinacochas</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-lg-end">
                <div class="d-flex gap-2 justify-content-lg-end mb-3">
                    <a href="#" class="social-link" aria-label="Facebook" target="_blank" rel="noopener">
                        <img src="{{ asset('assets/redes/facebook.png') }}" alt="Facebook">
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram" target="_blank" rel="noopener">
                        <img src="{{ asset('assets/redes/instagram.png') }}" alt="Instagram">
                    </a>
                    <a href="#" class="social-link" aria-label="LinkedIn" target="_blank" rel="noopener">
                        <img src="{{ asset('assets/redes/linkedin.png') }}" alt="LinkedIn">
                    </a>
                </div>
                <p class="small text-white-50 mb-0">&copy; {{ date('Y') }} EMPLEA LOCAL · Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
