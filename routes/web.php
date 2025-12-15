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

// Página de inicio (redirecciona al login)
Route::get('/', function () {
    return view('auth.login');
});

// Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== RUTAS PARA TRABAJADORES ==========
// SOLUCIÓN 1: Usa la clase directamente (RECOMENDADO)
Route::middleware([AuthenticateTrabajador::class])->group(function () {
    // Rutas principales para certificados empresariales
    Route::prefix('certificados-empresariales')->group(function () {
        Route::get('/', [CertificadoEController::class, 'index'])->name('certificados_e.index');
        Route::post('/buscar', [CertificadoEController::class, 'buscar'])->name('certificados_e.buscar');
        Route::post('/descargar-multiples', [CertificadoEController::class, 'descargarMultiples'])->name('certificados_e.descargarMultiples');
        Route::post('/generar-zip', [CertificadoEController::class, 'generarZip'])->name('certificados_e.generarZip');
    });
    
    // Rutas para ver/descargar documentos individuales
    Route::get('/documento/{id}/ver', [CertificadoEController::class, 'verDocumento'])->name('documento.ver');
    Route::get('/documento/{id}/descargar', [CertificadoEController::class, 'descargarDocumento'])->name('documento.descargar');
});

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