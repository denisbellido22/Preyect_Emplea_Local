<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Inicio')) · EMPLEA LOCAL</title>
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
        .footer-links { display: flex; flex-wrap: wrap; gap: .35rem 1rem; margin-top: .5rem; }
        .footer-links a { color: #9fb8b3; font-size: .8rem; text-decoration: none; }
        .footer-links a:hover { color: #fff; text-decoration: underline; }
        .site-footer .footer-logo { height: 52px; width: auto; display: block; }
        .social-link {
            display: inline-flex; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(255, 255, 255, .1);
            transition: transform .15s ease, background .15s ease;
        }
        .social-link:hover { background: rgba(255, 255, 255, .22); transform: translateY(-2px); }
        .social-link img { width: 22px; height: 22px; object-fit: contain; }

        /* Selector de idioma */
        .language-toggle { white-space: nowrap; }
        .language-menu .dropdown-item { display: flex; align-items: center; gap: .5rem; }
        .language-menu .dropdown-item.active,
        .language-menu .dropdown-item:active { background-color: var(--brand); color: #fff; }
        .language-menu .flag { font-size: 1.05rem; line-height: 1; }
        .language-menu form { margin: 0; }

        @media (max-width: 575.98px) {
            .navbar-logo { height: 46px; }
            .social-link { width: 36px; height: 36px; }
            .navbar-logo { height: 44px; }
            .hero-logo { height: 80px; }
        }

        /* ============================================================
           Portada pública: hero, estadísticas y oportunidades en vivo
           ============================================================ */
        .hero-section {
            position: relative;
            overflow: hidden;
            border-radius: 1.25rem;
            padding: clamp(1.5rem, 4vw, 3.25rem);
            color: #fff;
            background:
                radial-gradient(circle at 88% 12%, rgba(245, 158, 11, .30), transparent 42%),
                linear-gradient(135deg, #0f766e 0%, #115e59 55%, #0b3b37 100%);
            box-shadow: 0 20px 45px rgba(15, 118, 110, .22);
        }
        .hero-section::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .09) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
        }
        .hero-section > * { position: relative; z-index: 1; }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .74rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase;
            background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .22);
            padding: .35rem .7rem; border-radius: 999px; margin-bottom: 1rem;
        }
        .hero-title {
            font-size: clamp(1.9rem, 4vw, 3rem); font-weight: 800;
            letter-spacing: -.02em; line-height: 1.08; margin-bottom: .85rem;
        }
        .hero-subtitle {
            color: rgba(255, 255, 255, .86); font-size: 1.02rem; max-width: 560px; margin-bottom: 1.35rem;
        }
        .hero-search {
            display: flex; align-items: center; gap: .55rem;
            background: #fff; border-radius: .85rem; padding: .4rem .4rem .4rem 1rem;
            max-width: 620px; box-shadow: 0 14px 30px rgba(3, 29, 26, .22);
        }
        .hero-search > i { color: #64748b; font-size: 1.1rem; }
        .hero-search-input {
            border: 0; outline: 0; flex: 1; min-width: 0; height: 46px;
            font-size: .98rem; color: #0f172a; background: transparent;
        }
        .btn-hero-search {
            background: var(--brand-accent); border: 0; color: #3f2a00;
            font-weight: 700; border-radius: .65rem; padding: .7rem 1.15rem; white-space: nowrap;
            transition: background .15s ease, transform .15s ease;
        }
        .btn-hero-search:hover { background: #d97706; color: #fff; transform: translateY(-1px); }
        .hero-actions { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: 1.25rem; }
        .btn-hero-primary {
            background: #fff; color: var(--brand-dark); font-weight: 700;
            border: 1px solid #fff; border-radius: .65rem; padding: .65rem 1.15rem;
        }
        .btn-hero-primary:hover { background: #ecfdf5; color: var(--brand-dark); }
        .btn-hero-ghost {
            background: transparent; color: #fff; font-weight: 600;
            border: 1px solid rgba(255, 255, 255, .55); border-radius: .65rem; padding: .65rem 1.15rem;
        }
        .btn-hero-ghost:hover { background: rgba(255, 255, 255, .14); color: #fff; border-color: #fff; }
        .hero-trust {
            display: flex; flex-wrap: wrap; gap: 1.1rem; margin-top: 1.35rem;
            font-size: .8rem; color: rgba(255, 255, 255, .8);
        }
        .hero-trust span { display: inline-flex; align-items: center; gap: .35rem; }
        .hero-trust i { color: #fcd34d; }

        .hero-carousel {
            border-radius: 1.15rem; overflow: hidden;
            box-shadow: 0 18px 40px rgba(3, 20, 18, .35); background: #08211f;
        }
        .hero-carousel .carousel-item img {
            width: 100%; height: clamp(260px, 38vw, 430px); object-fit: cover; display: block;
        }
        .hero-carousel .carousel-caption {
            left: 0; right: 0; bottom: 0; text-align: left;
            padding: 2.5rem 1.25rem 1rem;
            background: linear-gradient(to top, rgba(4, 27, 25, .92), rgba(4, 27, 25, 0));
        }
        .hero-carousel .carousel-caption strong { display: block; font-size: 1.05rem; }
        .hero-carousel .carousel-caption span { font-size: .82rem; color: rgba(255, 255, 255, .85); }
        .hero-carousel .carousel-indicators [data-bs-target] { width: 8px; height: 8px; border-radius: 50%; }

        .stats-strip {
            display: grid; grid-template-columns: repeat(4, 1fr);
            background: #fff; border-radius: 1rem; margin-top: 1.25rem;
            padding: 1rem .5rem; box-shadow: 0 8px 24px rgba(15, 23, 42, .07);
        }
        .stat-item { text-align: center; padding: .4rem .75rem; border-right: 1px solid #e2e8f0; }
        .stat-item:last-child { border-right: 0; }
        .stat-value {
            display: block; font-size: clamp(1.4rem, 2.5vw, 1.9rem);
            font-weight: 800; line-height: 1.1; color: var(--brand-dark);
        }
        .stat-label {
            font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: #64748b;
        }

        .home-section, .oportunidades-section { margin-top: 2.75rem; scroll-margin-top: 90px; }
        .section-heading {
            display: flex; justify-content: space-between; align-items: flex-end;
            gap: 1rem; flex-wrap: wrap; margin-bottom: 1.1rem;
        }
        .section-kicker {
            display: inline-flex; align-items: center; gap: .35rem;
            font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
            color: var(--brand-dark); background: #ccfbf1;
            padding: .3rem .6rem; border-radius: 999px; margin-bottom: .5rem;
        }
        .section-title {
            font-size: clamp(1.35rem, 2.5vw, 1.85rem); font-weight: 800;
            letter-spacing: -.01em; margin: 0; color: #0f172a;
        }
        .section-subtitle { color: #64748b; margin: .35rem 0 0; max-width: 640px; font-size: .92rem; }
        .section-heading-side { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .section-count { font-size: .8rem; color: #64748b; }
        .live-indicator {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .76rem; font-weight: 700; color: #047857;
            background: #ecfdf5; border: 1px solid #a7f3d0;
            padding: .32rem .65rem; border-radius: 999px;
        }
        .live-dot {
            width: 8px; height: 8px; border-radius: 50%; background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, .65); animation: livePulse 1.9s infinite;
        }
        .live-indicator[data-estado="desconectado"] {
            color: #92400e; background: #fffbeb; border-color: #fde68a;
        }
        .live-indicator[data-estado="desconectado"] .live-dot {
            background: #f59e0b; animation: none;
        }
        @keyframes livePulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, .55); }
            70% { box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .realtime-alert {
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
            border-radius: .75rem; padding: .6rem .9rem; font-size: .85rem; margin-bottom: 1rem;
        }
        .opportunity-filters { display: flex; gap: .45rem; flex-wrap: wrap; margin-bottom: 1.15rem; }
        .filter-chip {
            border: 1px solid #d8e3e1; background: #fff; color: #334155;
            font-size: .8rem; font-weight: 600; border-radius: 999px;
            padding: .4rem .85rem; display: inline-flex; align-items: center; gap: .35rem;
            transition: all .15s ease;
        }
        .filter-chip:hover { border-color: var(--brand); color: var(--brand); }
        .filter-chip.is-active {
            background: var(--brand); border-color: var(--brand); color: #fff;
            box-shadow: 0 6px 14px rgba(15, 118, 110, .25);
        }

        .opportunity-grid {
            display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;
        }
        .opportunity-card {
            background: #fff; border: 1px solid #e8eeec; border-radius: 1rem;
            box-shadow: 0 6px 20px rgba(15, 23, 42, .06); overflow: hidden;
            display: flex; flex-direction: column;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .opportunity-card:hover {
            transform: translateY(-3px); box-shadow: 0 16px 32px rgba(15, 23, 42, .12);
        }
        #postular { scroll-margin-top: 90px; }
        .opportunity-card.is-new {
            animation: cardIn .45s ease both;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, .75), 0 16px 32px rgba(15, 23, 42, .12);
        }
        .opportunity-card--updated { animation: cardUpdated .9s ease; }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(-12px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes cardUpdated {
            0% { box-shadow: 0 0 0 3px rgba(15, 118, 110, .55); }
            100% { box-shadow: 0 6px 20px rgba(15, 23, 42, .06); }
        }
        .opportunity-media {
            height: 150px; flex: none; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            color: #fff; font-size: 2.4rem;
        }
        .opportunity-media img { width: 100%; height: 100%; object-fit: cover; }
        .opportunity-media.is-placeholder { background: linear-gradient(135deg, #ecfdf5, #ccfbf1); color: var(--brand); }
        .opportunity-body {
            padding: 1rem 1.05rem 1.1rem; display: flex; flex-direction: column; flex: 1;
        }
        .opportunity-tags { display: flex; align-items: center; gap: .4rem; margin-bottom: .5rem; }
        .opportunity-tag {
            font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            padding: .28rem .55rem; border-radius: 999px; background: #f1f5f9; color: #334155;
        }
        .opportunity-new {
            font-size: .62rem; font-weight: 800; letter-spacing: .08em;
            background: #fef3c7; color: #92400e; border: 1px solid #fde68a;
            padding: .24rem .45rem; border-radius: 999px;
        }
        .opportunity-title { font-size: 1.02rem; font-weight: 700; line-height: 1.35; margin: 0 0 .3rem; }
        .opportunity-title a { color: #0f172a; text-decoration: none; }
        .opportunity-title a:hover { color: var(--brand); }
        .opportunity-institution {
            display: flex; align-items: center; gap: .35rem;
            color: #475569; font-size: .82rem; margin: 0 0 .45rem;
        }
        .opportunity-description { color: #64748b; font-size: .86rem; flex: 1; margin: 0 0 .7rem; }
        .opportunity-meta {
            list-style: none; padding: 0; margin: 0 0 .85rem;
            display: flex; flex-wrap: wrap; gap: .35rem .9rem;
            color: #64748b; font-size: .76rem;
        }
        .opportunity-meta li { display: inline-flex; align-items: center; gap: .3rem; }
        .opportunity-footer {
            display: flex; align-items: center; justify-content: space-between;
            gap: .5rem; margin-top: auto;
        }
        .opportunity-salary { font-weight: 700; color: var(--brand-dark); font-size: .9rem; }
        .opportunity-state { font-size: .72rem; color: #64748b; }

        .cat-empleo { --cat-bg: #dbeafe; --cat-fg: #1d4ed8; }
        .cat-beca { --cat-bg: #ede9fe; --cat-fg: #6d28d9; }
        .cat-convocatoria { --cat-bg: #ffedd5; --cat-fg: #c2410c; }
        .cat-examen { --cat-bg: #fee2e2; --cat-fg: #b91c1c; }
        .cat-practica { --cat-bg: #cffafe; --cat-fg: #0e7490; }
        .cat-capacitacion, .cat-curso { --cat-bg: #dcfce7; --cat-fg: #15803d; }
        .cat-charla, .cat-evento { --cat-bg: #fef9c3; --cat-fg: #a16207; }
        .cat-otra { --cat-bg: #f1f5f9; --cat-fg: #475569; }
        .opportunity-tag[class*="cat-"] { background: var(--cat-bg); color: var(--cat-fg); }

        .empty-state {
            text-align: center; padding: 2.5rem 1rem; background: #fff;
            border: 1px dashed #cbd5e1; border-radius: 1rem; color: #64748b;
        }
        .empty-state i { font-size: 2rem; color: #94a3b8; }
        .empty-state h3 { font-size: 1.05rem; color: #334155; margin: .5rem 0 .2rem; }
        .empty-state p { font-size: .88rem; margin: 0; }
        .oportunidades-acciones {
            display: flex; gap: .6rem; flex-wrap: wrap;
            justify-content: center; margin-top: 1.4rem;
        }
        .realtime-notifications {
            position: fixed; right: 1rem; bottom: 1.25rem; z-index: 1080;
            display: flex; flex-direction: column; gap: .5rem; max-width: 320px;
        }
        .realtime-toast {
            background: #0f2e2b; color: #fff; border-radius: .75rem;
            padding: .7rem .9rem; border-left: 4px solid var(--brand-accent);
            box-shadow: 0 14px 30px rgba(2, 20, 18, .3);
            display: flex; flex-direction: column; gap: .15rem;
            animation: toastIn .3s ease both;
        }
        .realtime-toast strong { font-size: .8rem; color: #fcd34d; }
        .realtime-toast span { font-size: .82rem; color: #e2e8f0; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .category-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .85rem; }
        .category-card {
            position: relative; display: flex; flex-direction: column; gap: .15rem;
            background: #fff; border: 1px solid #e8eeec; border-radius: .9rem;
            padding: 1rem 1.1rem; text-decoration: none; color: inherit;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .category-card:hover {
            border-color: var(--brand); transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, .1);
        }
        .category-icon {
            width: 42px; height: 42px; border-radius: .7rem; margin-bottom: .4rem;
            display: inline-flex; align-items: center; justify-content: center;
            background: #ccfbf1; color: var(--brand-dark); font-size: 1.2rem;
        }
        .category-card .category-icon { background: var(--cat-bg, #ccfbf1); color: var(--cat-fg, var(--brand-dark)); }
        .category-name { font-weight: 700; font-size: .98rem; color: #0f172a; }
        .category-text { font-size: .8rem; color: #64748b; }
        .category-arrow { position: absolute; top: 1rem; right: 1rem; color: #94a3b8; }

        .featured-list { display: grid; gap: .6rem; }
        .featured-item {
            display: flex; align-items: center; gap: .85rem;
            background: #fff; border: 1px solid #e8eeec; border-radius: .85rem;
            padding: .8rem 1rem; text-decoration: none; color: inherit;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .featured-item:hover { border-color: var(--brand); box-shadow: 0 10px 24px rgba(15, 23, 42, .08); }
        .featured-icon {
            flex: none; width: 44px; height: 44px; border-radius: .7rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; background: var(--cat-bg, #ccfbf1); color: var(--cat-fg, var(--brand-dark));
        }
        .featured-content { display: flex; flex-direction: column; gap: .1rem; flex: 1; min-width: 0; }
        .featured-tag { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; color: var(--brand); }
        .featured-title { font-size: .95rem; color: #0f172a; }
        .featured-meta { font-size: .78rem; color: #64748b; }
        .featured-arrow { color: #94a3b8; }

        .institution-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
        .institution-chip {
            display: flex; align-items: center; gap: .65rem;
            background: #fff; border: 1px solid #e8eeec; border-radius: .8rem;
            padding: .7rem .85rem; font-size: .85rem; font-weight: 600; color: #334155;
        }
        .institution-initial {
            flex: none; width: 34px; height: 34px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f766e, #14b8a6); color: #fff; font-weight: 800;
        }

        .benefit-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        .benefit-card {
            background: #fff; border: 1px solid #e8eeec; border-radius: 1rem;
            padding: 1.25rem; box-shadow: 0 6px 20px rgba(15, 23, 42, .05);
        }
        .benefit-icon {
            width: 46px; height: 46px; border-radius: .8rem; margin-bottom: .6rem;
            display: inline-flex; align-items: center; justify-content: center;
            background: #fef3c7; color: #b45309; font-size: 1.3rem;
        }
        .benefit-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: .35rem; }
        .benefit-card p { font-size: .85rem; color: #64748b; margin: 0; }

        @media (max-width: 991.98px) {
            .opportunity-grid, .category-grid, .benefit-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .institution-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hero-carousel .carousel-item img { height: clamp(220px, 50vw, 340px); }
        }
        @media (max-width: 575.98px) {
            .opportunity-grid, .category-grid, .benefit-grid, .institution-grid { grid-template-columns: 1fr; }
            .stats-strip { grid-template-columns: repeat(2, 1fr); gap: .75rem 0; }
            .stat-item { border-right: 0; }
            .hero-search { flex-wrap: wrap; padding: .6rem; }
            .hero-search-input { flex-basis: calc(100% - 2rem); height: 40px; }
            .btn-hero-search { width: 100%; }
            .hero-section { border-radius: 1rem; }
            .realtime-notifications { left: 1rem; right: 1rem; max-width: none; }
            .section-heading { align-items: flex-start; }
        }
    </style>
    @vite(['resources/js/app.js'])
</head>
<body data-role="{{ auth()->check() ? auth()->user()->role : 'invitado' }}">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}" aria-label="EMPLEA LOCAL - {{ __('Inicio') }}">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="EMPLEA LOCAL" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">{{ __('Inicio') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('ofertas.index') }}">{{ __('Empleos') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('oportunidades.index') }}">{{ __('Becas y oportunidades') }}</a></li>
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#sobre-la-plataforma">{{ __('La plataforma') }}</a></li>
                @endguest
                @auth
                    @if(auth()->user()->role === 'empleador')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ __('Empleador') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('empleador.ofertas.index') }}">{{ __('Mis ofertas') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('empleador.ofertas.create') }}">{{ __('Nueva oferta') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('empleador.postulantes.index') }}">{{ __('Postulantes') }}</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('empleador.publicidades.index') }}">{{ __('Mi publicidad') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('empleador.publicidades.create') }}">{{ __('Solicitar publicidad') }}</a></li>
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->role === 'postulante')
                        <li class="nav-item"><a class="nav-link" href="{{ route('postulaciones.index') }}">{{ __('Mis postulaciones') }}</a></li>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ __('Administración') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.usuarios.index') }}">{{ __('Usuarios') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.ofertas.index') }}">{{ __('Moderar ofertas') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.oportunidades.index') }}">{{ __('Oportunidades') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.publicidades.index') }}">{{ __('Publicidad') }}</a></li>
                            </ul>
                        </li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                {{-- Selector de idioma: cambia toda la interfaz sin salir de la página --}}
                @php($banderas = ['es' => '🇪🇸', 'en' => '🇺🇸', 'pt' => '🇵🇹'])
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle language-toggle" href="#" id="selectorIdioma"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       aria-label="{{ __('Cambiar idioma') }}" title="{{ __('Cambiar idioma') }}">
                        <i class="bi bi-globe2 me-1"></i><span class="fw-semibold">{{ strtoupper(app()->getLocale()) }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end language-menu shadow-sm" aria-labelledby="selectorIdioma">
                        @foreach(config('app.supported_locales') as $codigo => $etiqueta)
                            <li>
                                <form action="{{ route('locale.update', $codigo) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="dropdown-item {{ app()->getLocale() === $codigo ? 'active' : '' }}"
                                            @if(app()->getLocale() === $codigo) aria-current="true" @endif>
                                        <span class="flag">{{ $banderas[$codigo] ?? '🌐' }}</span>
                                        <span>{{ $etiqueta }}</span>
                                        @if(app()->getLocale() === $codigo)
                                            <i class="bi bi-check2 ms-auto"></i>
                                        @endif
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </li>

                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Ingresar') }}</a></li>
                    <li class="nav-item"><a class="btn btn-brand btn-sm mt-1 ms-lg-2" href="{{ route('register') }}">{{ __('Registrarse') }}</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->nombreCompleto() }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small">{{ __('role.'.auth()->user()->role) }}</span></li>
                            <li>
                                <a class="dropdown-item" href="{{ auth()->user()->urlCorreo() }}"
                                   target="_blank" rel="noopener noreferrer"
                                   title="{{ __('Abrir mi correo electrónico') }}">
                                    <i class="bi bi-envelope me-1"></i>
                                    <span class="small">{{ auth()->user()->email }}</span>
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('perfil.show') }}">{{ __('Mi perfil') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('perfil.edit') }}">{{ __('Editar perfil') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('perfil.password.edit') }}">{{ __('Cambiar contraseña') }}</a></li>
                            <li><a class="dropdown-item text-danger" href="{{ route('perfil.show') }}#eliminar-cuenta">{{ __('Eliminar mi cuenta') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">{{ __('Cerrar sesión') }}</button>
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
            <strong><i class="bi bi-x-circle me-1"></i>{{ __('Revisa los siguientes errores:') }}</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @auth
        @if(! auth()->user()->hasVerifiedEmail() && ! request()->routeIs('verification.notice'))
            <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
                <div>
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ __('Tu correo :email aún no está verificado.', ['email' => auth()->user()->email]) }}
                </div>
                <a href="{{ route('verification.notice') }}" class="btn btn-brand btn-sm">
                    <i class="bi bi-envelope-check me-1"></i>{{ __('Verificar mi correo') }}
                </a>
            </div>
        @endif
    @endauth

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
                        <div class="small text-white-50">{{ __('Conectando oportunidades laborales en Parinacochas') }}</div>
                        <nav class="footer-links" aria-label="{{ __('Enlaces del sitio') }}">
                            <a href="{{ route('ofertas.index') }}">{{ __('Empleos') }}</a>
                            <a href="{{ route('oportunidades.index') }}">{{ __('Becas y oportunidades') }}</a>
                            <a href="{{ route('home') }}#sobre-la-plataforma">{{ __('La plataforma') }}</a>
                            @guest
                                <a href="{{ route('login') }}">{{ __('Ingresar') }}</a>
                            @endguest
                        </nav>
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
                <p class="small text-white-50 mb-0">&copy; {{ date('Y') }} EMPLEA LOCAL · {{ __('Todos los derechos reservados.') }}</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
