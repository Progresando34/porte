<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArmaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificadoEController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Middleware\AuthenticateTrabajador;
use App\Http\Controllers\ClienteCertificadoController;
use App\Http\Controllers\RayosXController;
use Illuminate\Support\Facades\Schema;

// Página de inicio
Route::get('/', function () {
    return view('auth.login');
});

// Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== RUTAS PARA TODOS LOS USUARIOS ==========
// Ruta principal - según autenticación se redirige
Route::prefix('certificados-empresariales')->group(function () {
    // Vista para clientes (usuarios normales)
    Route::get('/', [CertificadoEController::class, 'index'])->name('certificados_e.index');
    
    // Vista específica para trabajadores
    Route::get('/trabajador', [CertificadoEController::class, 'indexTrabajador'])->name('certificados_e.trabajador');
    
    // Búsqueda (usa el mismo método para ambos)
    Route::post('/buscar', [CertificadoEController::class, 'buscar'])->name('certificados_e.buscar');
    
    // Descarga múltiple
    Route::post('/descargar-multiples', [CertificadoEController::class, 'descargarMultiples'])->name('certificados_e.descargarMultiples');
});


// ========== RUTAS PARA CLIENTES ==========
// Usan el NUEVO controlador SIN restricciones
Route::prefix('cliente')->group(function () {
    Route::get('/certificados', [ClienteCertificadoController::class, 'index'])
        ->name('cliente.certificados.index');
    
    Route::post('/certificados/buscar', [ClienteCertificadoController::class, 'buscar'])
        ->name('cliente.certificados.buscar');
});

// ========== RUTAS PARA TRABAJADORES ==========
// Usan el controlador original CON restricciones
Route::prefix('trabajador')->group(function () {
    Route::get('/certificados', [CertificadoEController::class, 'indexTrabajador'])
        ->name('trabajador.certificados.index');
    
    Route::post('/certificados/buscar', [CertificadoEController::class, 'buscar'])
        ->name('trabajador.certificados.buscar');

        Route::get('/actualizar-password', [AuthController::class, 'showChangePasswordForm'])
        ->name('trabajadores.actualizar-password-form');
    
    Route::put('/actualizar-password', [AuthController::class, 'updatePassword'])
        ->name('trabajadores.actualizar-password');
});

// ========== RUTAS PARA DOCUMENTOS ==========
Route::get('/documento/{id}/ver', [CertificadoEController::class, 'verDocumento'])->name('documento.ver');
Route::get('/documento/{id}/descargar', [CertificadoEController::class, 'descargarDocumento'])->name('documento.descargar');

// ========== RUTAS PARA USUARIOS ADMIN ==========
Route::middleware('auth')->group(function () {
    // CRUD completo de usuarios
    Route::resource('usuarios', UserController::class);
    
    // CRUD completo de trabajadores
    Route::resource('trabajadores', TrabajadorController::class);
    
    // Dashboard de administración
    Route::get('/admin/dashboard', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    // Rutas CRUD de archivos
    Route::get('/archivos', [ArchivoController::class, 'index'])->name('archivos.index');
    Route::post('/archivos', [ArchivoController::class, 'store'])->name('archivos.store');
    Route::delete('/archivos/{archivo}', [ArchivoController::class, 'destroy'])->name('archivos.destroy');
    
    // Vistas simples de armas
    Route::get('/armas/create', fn() => view('armas.create'))->name('armas.create');
    Route::get('/armas/docs', fn() => view('armas.docs'))->name('armas.docs');
    Route::get('/armas/licita', fn() => view('armas.licita'))->name('armas.licita');
    
    // Rutas para certificados
    Route::get('/certificados/crear', [CertificadoController::class, 'create'])->name('certificados.create');
    Route::post('/certificados', [CertificadoController::class, 'store'])->name('certificados.store');
  
     // Rayos X
    Route::get('/rayosx', [RayosXController::class, 'index'])->name('rayosx.index');
    Route::get('/rayosx/create', [RayosXController::class, 'create'])->name('rayosx.create');
    Route::post('/rayosx', [RayosXController::class, 'store'])->name('rayosx.store');

});

// ========== RUTAS PÚBLICAS ==========
Route::get('/ver-certificado/{filename}', [CertificadoController::class, 'ver'])->name('ver.certificado');
Route::get('/descargar-certificado/{filename}', [CertificadoController::class, 'descargar'])->name('descargar.certificado');
Route::get('/client/consultaArmas', [ArmaController::class, 'consulta'])->name('client.consultaArmas');
Route::get('/armas/ver/{filename}', [ArmaController::class, 'ver'])->name('armas.ver.certificado');
Route::get('/armas/descargar/{filename}', [ArmaController::class, 'descargar'])->name('armas.descargar.certificado');
Route::post('/descargar-multiples-certificados', [ArmaController::class, 'descargarMultiples'])->name('descargar.multiples');

// ========== RUTAS DE DEPURACIÓN ==========
Route::get('/debug-session', function () {
    return [
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'is_trabajador' => session()->has('trabajador_autenticado'),
        'trabajador_nombre' => session('trabajador_nombre', 'NO'),
        'cookies' => request()->cookies->all(),
    ];
});

Route::get('/test-tabla', function () {
    if (Schema::hasTable('rayosxod')) {
        return '✅ La tabla SI existe';
    } else {
        return '❌ La tabla NO existe';
    }
});



// CORREGIDO: Usa la clase directamente
Route::get('/test-trabajador', function () {
    if (!session()->has('trabajador_autenticado')) {
        return 'NO eres trabajador autenticado';
    }
    return '✅ Eres trabajador: ' . session('trabajador_nombre');
})->middleware([AuthenticateTrabajador::class]);  // ← CORREGIDO

Route::get('/set-trabajador-session', function () {
    // Ruta para simular login manualmente
    session([
        'trabajador_id' => 1,
        'trabajador_nombre' => 'Juan Pérez',
        'trabajador_cedula' => '12345678',
        'trabajador_usuario' => 'juan.perez',
        'trabajador_autenticado' => true
    ]);
    
    return 'Sesión de trabajador establecida. <a href="/test-trabajador">Probar</a>';
});

// CRUD completo para armas
Route::resource('armas', ArmaController::class);

// ========== RUTAS DE DEBUG ==========
Route::get('/debug-estructura', [CertificadoEController::class, 'debugEstructura']);
Route::get('/test-controlador', [CertificadoEController::class, 'index']);
Route::get('/debug-test', function () {
    \Illuminate\Support\Facades\Log::info('🧪 RUTA DE PRUEBA FUNCIONA');
    return '✅ Esta ruta está activa';
});

// ========== RUTAS PARA RAYOS X ==========
Route::get('/rayos/{id}/ver', [CertificadoEController::class, 'verRayos'])->name('rayos.ver');
Route::get('/rayos/{id}/descargar', [CertificadoEController::class, 'descargarRayos'])->name('rayos.descargar');

Route::get('/debug-cedula', [CertificadoEController::class, 'debugDirecto']);

Route::get('/ver-rx/{id}', function ($id) {
    $rx = \App\Models\RayosX::findOrFail($id);
    $path = storage_path('app/public/' . $rx->ruta);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});