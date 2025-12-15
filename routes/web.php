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

// Página de inicio (redirecciona al login)
Route::get('/', function () {
    return view('auth.login');
});

// Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== RUTAS PARA TRABAJADORES ==========
// Estas rutas SOLO pueden ser accedidas por trabajadores autenticados
Route::middleware('trabajador.auth')->group(function () {
    // Rutas principales para certificados empresariales
    Route::prefix('certificados-empresariales')->group(function () {
        Route::get('/', [CertificadoEController::class, 'index'])->name('certificados_e.index');
        Route::post('/buscar', [CertificadoEController::class, 'buscar'])->name('certificados_e.buscar');
        Route::post('/descargar-multiples', [CertificadoEController::class, 'descargarMultiples'])->name('certificados_e.descargarMultiples');
        Route::post('/generar-zip', [CertificadoEController::class, 'generarZip'])->name('certificados_e.generarZip');
    });
    
    // Rutas para ver/descargar documentos individuales (para trabajadores)
    Route::get('/documento/{id}/ver', [CertificadoEController::class, 'verDocumento'])->name('documento.ver');
    Route::get('/documento/{id}/descargar', [CertificadoEController::class, 'descargarDocumento'])->name('documento.descargar');
});

// ========== RUTAS PARA USUARIOS ADMIN ==========
// Estas rutas SOLO pueden ser accedidas por usuarios admin (Auth)
Route::middleware('auth')->group(function () {
    // CRUD completo de usuarios
    Route::resource('usuarios', UserController::class);
    
    // CRUD completo de trabajadores (solo admin puede gestionarlos)
    Route::resource('trabajadores', TrabajadorController::class);
    
    // Dashboard de administración
    Route::get('/admin/dashboard', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    // Rutas CRUD de archivos
    Route::get('/archivos', [ArchivoController::class, 'index'])->name('archivos.index');
    Route::post('/archivos', [ArchivoController::class, 'store'])->name('archivos.store');
    Route::delete('/archivos/{archivo}', [ArchivoController::class, 'destroy'])->name('archivos.destroy');
    
    // Vistas simples de armas (solo admin)
    Route::get('/armas/create', fn() => view('armas.create'))->name('armas.create');
    Route::get('/armas/docs', fn() => view('armas.docs'))->name('armas.docs');
    Route::get('/armas/licita', fn() => view('armas.licita'))->name('armas.licita');
    
    // Rutas para certificados (solo admin)
    Route::get('/certificados/crear', [CertificadoController::class, 'create'])->name('certificados.create');
    Route::post('/certificados', [CertificadoController::class, 'store'])->name('certificados.store');
});

// ========== RUTAS PÚBLICAS ==========
// Estas rutas NO requieren autenticación
Route::get('/ver-certificado/{filename}', [CertificadoController::class, 'ver'])->name('ver.certificado');
Route::get('/descargar-certificado/{filename}', [CertificadoController::class, 'descargar'])->name('descargar.certificado');
Route::get('/client/consultaArmas', [ArmaController::class, 'consulta'])->name('client.consultaArmas');
Route::get('/armas/ver/{filename}', [ArmaController::class, 'ver'])->name('armas.ver.certificado');
Route::get('/armas/descargar/{filename}', [ArmaController::class, 'descargar'])->name('armas.descargar.certificado');
Route::post('/descargar-multiples-certificados', [ArmaController::class, 'descargarMultiples'])->name('descargar.multiples');

// CRUD completo para armas (público o según necesites)
Route::resource('armas', ArmaController::class);

// ========== RUTAS DE DEBUG ==========
// Opcionales, para desarrollo
Route::get('/debug-estructura', [CertificadoEController::class, 'debugEstructura']);
Route::get('/test-controlador', [CertificadoEController::class, 'index']);
Route::get('/debug-test', function () {
    \Illuminate\Support\Facades\Log::info('🧪 RUTA DE PRUEBA FUNCIONA');
    return '✅ Esta ruta está activa';
});