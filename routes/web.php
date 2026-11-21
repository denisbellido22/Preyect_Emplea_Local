<?php

use App\Http\Controllers\Admin\OfertaController as AdminOfertaController;
use App\Http\Controllers\Admin\OportunidadController as AdminOportunidadController;
use App\Http\Controllers\Admin\PublicidadController as AdminPublicidadController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\OportunidadController;
use App\Http\Controllers\PostulacionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicidadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Cambio de idioma de la interfaz (español / inglés). Disponible para
// autenticados y visitantes; el idioma se guarda en sesión y en una cookie.
Route::post('/idioma/{locale}', [LocaleController::class, 'update'])->name('locale.update');

// RF07 / RF08: búsqueda y detalle de ofertas (accesibles a todos)
Route::get('/ofertas', [OfertaController::class, 'index'])->name('ofertas.index');
Route::get('/ofertas/{oferta}', [OfertaController::class, 'show'])->name('ofertas.show');

// RF15: las oportunidades publicadas también son visibles para visitantes.
Route::get('/oportunidades', [OportunidadController::class, 'index'])->name('oportunidades.index');
Route::get('/oportunidades/{oportunidad}', [OportunidadController::class, 'show'])->name('oportunidades.show');

/*
|--------------------------------------------------------------------------
| Autenticación (RF01 / RF02)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // RF02: recuperación de contraseña por correo electrónico
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Verificación de correo electrónico
|--------------------------------------------------------------------------
| Rutas nativas de Laravel protegidas con el middleware "signed": el enlace
| incluye la firma, el id del usuario y el hash de su correo, de modo que no
| se puede verificar una cuenta manipulando la URL.
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verify/resend', [EmailVerificationController::class, 'send'])->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas (cualquier rol)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // RF03: perfil
    Route::get('/perfil', [ProfileController::class, 'show'])->name('perfil.show');
    Route::get('/perfil/editar', [ProfileController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('perfil.update');

    // RF03: cambio de contraseña (disponible para todos los roles)
    Route::get('/perfil/password', [ProfileController::class, 'editPassword'])->name('perfil.password.edit');
    Route::put('/perfil/password', [ProfileController::class, 'updatePassword'])->name('perfil.password.update');

    // RF03: eliminación de la propia cuenta (requiere contraseña actual)
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('perfil.destroy');


    /*
    |----------------------------------------------------------------------
    | EMPLEADOR (RF04, RF05, RF12, RF16)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:empleador')->prefix('empleador')->name('empleador.')->group(function () {
        Route::get('/ofertas', [OfertaController::class, 'misOfertas'])->name('ofertas.index');
        // Publicar o modificar ofertas exige un correo verificado.
        Route::get('/ofertas/crear', [OfertaController::class, 'create'])->middleware('verified')->name('ofertas.create');
        Route::post('/ofertas', [OfertaController::class, 'store'])->middleware('verified')->name('ofertas.store');
        Route::get('/ofertas/{oferta}/editar', [OfertaController::class, 'edit'])->middleware('verified')->name('ofertas.edit');
        Route::put('/ofertas/{oferta}', [OfertaController::class, 'update'])->middleware('verified')->name('ofertas.update');
        Route::delete('/ofertas/{oferta}', [OfertaController::class, 'destroy'])->middleware('verified')->name('ofertas.destroy');

        Route::get('/postulantes', [PostulacionController::class, 'postulantes'])->name('postulantes.index');
        Route::put('/postulaciones/{postulacion}/estado', [PostulacionController::class, 'actualizarEstado'])->name('postulaciones.estado');

        Route::get('/publicidades', [PublicidadController::class, 'index'])->name('publicidades.index');
        Route::get('/publicidades/crear', [PublicidadController::class, 'create'])->middleware('verified')->name('publicidades.create');
        Route::post('/publicidades', [PublicidadController::class, 'store'])->middleware('verified')->name('publicidades.store');
    });

    /*
    |----------------------------------------------------------------------
    | POSTULANTE (RF09, RF10, RF11)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:postulante')->group(function () {
        // Postularse envía datos personales al empleador: exige correo verificado.
        Route::post('/ofertas/{oferta}/postular', [PostulacionController::class, 'store'])
            ->middleware('verified')
            ->name('postulaciones.store');
        Route::get('/mis-postulaciones', [PostulacionController::class, 'index'])->name('postulaciones.index');
    });

    /*
    |----------------------------------------------------------------------
    | ADMINISTRADOR (RF06, RF13, RF14, RF17, RF19)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // RF13: gestión de usuarios
        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/{usuario}/editar', [AdminUserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}', [AdminUserController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [AdminUserController::class, 'destroy'])->name('usuarios.destroy');

        // RF06: moderación de ofertas
        Route::get('/ofertas', [AdminOfertaController::class, 'index'])->name('ofertas.index');
        Route::post('/ofertas/{oferta}/aprobar', [AdminOfertaController::class, 'aprobar'])->name('ofertas.aprobar');
        Route::post('/ofertas/{oferta}/rechazar', [AdminOfertaController::class, 'rechazar'])->name('ofertas.rechazar');
        Route::get('/ofertas/{oferta}/editar', [AdminOfertaController::class, 'edit'])->name('ofertas.edit');
        Route::put('/ofertas/{oferta}', [AdminOfertaController::class, 'update'])->name('ofertas.update');
        Route::delete('/ofertas/{oferta}', [AdminOfertaController::class, 'destroy'])->name('ofertas.destroy');

        // RF14: gestión de oportunidades
        Route::resource('/oportunidades', AdminOportunidadController::class)
            ->except(['show'])
            ->parameters(['oportunidades' => 'oportunidad'])
            ->names('oportunidades');

        // RF17: gestión de publicidad
        Route::get('/publicidades', [AdminPublicidadController::class, 'index'])->name('publicidades.index');
        Route::post('/publicidades/{publicidad}/aprobar', [AdminPublicidadController::class, 'aprobar'])->name('publicidades.aprobar');
        Route::post('/publicidades/{publicidad}/rechazar', [AdminPublicidadController::class, 'rechazar'])->name('publicidades.rechazar');
        Route::post('/publicidades/{publicidad}/finalizar', [AdminPublicidadController::class, 'finalizar'])->name('publicidades.finalizar');
    });
});
