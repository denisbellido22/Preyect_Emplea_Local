<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OfertaController;
use App\Http\Controllers\Api\OportunidadController;
use App\Http\Controllers\Api\PostulacionController;
use App\Http\Controllers\PostController;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API REST de Emplea Local
|--------------------------------------------------------------------------
| No existe una tabla "posts": las publicaciones son ofertas de empleo y
| oportunidades reales del sistema. Los endpoints públicos no requieren
| autenticación; los protegidos usan tokens de Laravel Sanctum:
|
|     Authorization: Bearer <token>
|
| Documentación completa: docs/API_REST.md
| Colección Postman:      docs/postman/Emplea_Local_API.postman_collection.json
*/

/*
|--------------------------------------------------------------------------
| Consultas públicas (visitantes sin token)
|--------------------------------------------------------------------------
*/
Route::get('/publicaciones', [PostController::class, 'index']);
Route::get('/publicaciones/{tipo}/{id}', [PostController::class, 'show'])
    ->whereIn('tipo', ['oferta', 'oportunidad']);

// Empleos (ofertas). Se mantienen ambos prefijos: /ofertas y /empleos.
Route::get('/ofertas/recientes', [OfertaController::class, 'recientes']);
Route::get('/ofertas', [OfertaController::class, 'index']);
Route::get('/ofertas/{oferta}', [OfertaController::class, 'show']);

Route::get('/empleos/recientes', [OfertaController::class, 'recientes']);
Route::get('/empleos', [OfertaController::class, 'index']);
Route::get('/empleos/{oferta}', [OfertaController::class, 'show']);

// Oportunidades: becas, convocatorias, exámenes, prácticas, cursos, eventos.
Route::get('/oportunidades/recientes', [OportunidadController::class, 'recientes']);
Route::get('/oportunidades', [OportunidadController::class, 'index']);
Route::get('/oportunidades/{oportunidad}', [OportunidadController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Autenticación (tokens personales de Sanctum)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (cualquier rol autenticado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Ruta conservada por compatibilidad con la instalación original de Sanctum.
    Route::get('/user', fn (Request $request) => ApiResponse::success(
        new UserResource($request->user()),
        'Usuario autenticado obtenido correctamente.'
    ));

    // Publicaciones unificadas (oferta u oportunidad según "tipo_publicacion").
    Route::post('/publicaciones', [PostController::class, 'store']);
    Route::match(['put', 'patch'], '/publicaciones/{tipo}/{id}', [PostController::class, 'update'])
        ->whereIn('tipo', ['oferta', 'oportunidad']);
    Route::delete('/publicaciones/{tipo}/{id}', [PostController::class, 'destroy'])
        ->whereIn('tipo', ['oferta', 'oportunidad']);

    // Gestión de ofertas propias (el empleador no puede autoaprobarse).
    Route::put('/ofertas/{oferta}', [OfertaController::class, 'update']);
    Route::patch('/ofertas/{oferta}', [OfertaController::class, 'update']);
    Route::delete('/ofertas/{oferta}', [OfertaController::class, 'destroy']);

    // Postulaciones: cada rol ve únicamente lo que le corresponde.
    Route::get('/postulaciones', [PostulacionController::class, 'index']);
    Route::get('/postulaciones/{postulacion}', [PostulacionController::class, 'show']);

    // Empleador: publicar ofertas y evaluar a los postulantes de sus ofertas.
    Route::middleware('api.role:empleador')->group(function () {
        Route::post('/ofertas', [OfertaController::class, 'store']);
    });

    Route::middleware('api.role:empleador,admin')->group(function () {
        Route::get('/ofertas/{oferta}/postulantes', [PostulacionController::class, 'postulantes']);
        Route::patch('/postulaciones/{postulacion}/estado', [PostulacionController::class, 'actualizarEstado']);
        Route::put('/postulaciones/{postulacion}/estado', [PostulacionController::class, 'actualizarEstado']);
    });

    // Postulante: sólo puede postularse a ofertas aprobadas.
    Route::middleware('api.role:postulante')->group(function () {
        Route::post('/ofertas/{oferta}/postulaciones', [PostulacionController::class, 'store']);
    });

    /*
    |----------------------------------------------------------------------
    | Administrador: moderación y gestión de oportunidades (RF06 / RF14)
    |----------------------------------------------------------------------
    */
    Route::middleware('api.role:admin')->group(function () {
        Route::patch('/admin/ofertas/{oferta}/estado', [OfertaController::class, 'actualizarEstado']);

        Route::post('/oportunidades', [OportunidadController::class, 'store']);
        Route::put('/oportunidades/{oportunidad}', [OportunidadController::class, 'update']);
        Route::patch('/oportunidades/{oportunidad}', [OportunidadController::class, 'update']);
        Route::delete('/oportunidades/{oportunidad}', [OportunidadController::class, 'destroy']);
    });
});
