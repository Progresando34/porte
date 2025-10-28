<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArmaController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificadoEController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

// Página de inicio (redirecciona al login)
Route::get('/', function () {
    return view('auth.login');
});

// Rutas CRUD de archivos
Route::get('/archivos', [ArchivoController::class, 'index'])->name('archivos.index');
Route::post('/archivos', [ArchivoController::class, 'store'])->name('archivos.store');
Route::delete('/archivos/{archivo}', [ArchivoController::class, 'destroy'])->name('archivos.destroy');

// Vistas simples
Route::get('/armas/create', fn() => view('armas.create'))->name('armas.create');
Route::get('/armas/docs', fn() => view('armas.docs'))->name('armas.docs');
Route::get('/armas/licita', fn() => view('armas.licita'))->name('armas.licita');

// Rutas para certificados
Route::get('/ver-certificado/{filename}', [CertificadoController::class, 'ver'])->name('ver.certificado');
Route::get('/descargar-certificado/{filename}', [CertificadoController::class, 'descargar'])->name('descargar.certificado');
Route::get('/certificados/crear', [CertificadoController::class, 'create'])->name('certificados.create');
Route::post('/certificados', [CertificadoController::class, 'store'])->name('certificados.store');

// Consultas de clientes y armas
Route::match(['GET', 'POST'], 'client/consulta', [ClientController::class, 'consulta'])->name('client.consulta');
Route::get('/client/consultaArmas', [ArmaController::class, 'consulta'])->name('client.consultaArmas');
Route::get('/consulta', [ClientController::class, 'consulta']);
Route::get('/consultaArmas', [ArmaController::class, 'consulta']);

// CRUD completo para armas
Route::resource('armas', ArmaController::class);

// Rutas personalizadas para ver/descargar certificados de armas
Route::get('/armas/ver/{filename}', [ArmaController::class, 'ver'])->name('armas.ver.certificado');
Route::get('/armas/descargar/{filename}', [ArmaController::class, 'descargar'])->name('armas.descargar.certificado');

// Rutas de usuarios
Route::get('/usuarios/crear', [UserController::class, 'create'])->name('usuarios.create');
Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');

// Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Certificados especiales
Route::get('/descargar-multiples-certificados', [ArmaController::class, 'descargarMultiples'])->name('descargar.multiples');
Route::get('/certificados_e', [CertificadoEController::class, 'index'])->name('certificados_e.index');
Route::post('/certificados_e/buscar', [CertificadoEController::class, 'buscar'])->name('certificados_e.buscar');
Route::get('/certificados/descargar-multiples', [CertificadoEController::class, 'descargarMultiples'])->name('certificados_e.descargarMultiples');

// Ruta de prueba (debug)
Route::get('/debug-test', function () {
    Log::info('🧪 RUTA DE PRUEBA FUNCIONA');
    return '✅ Esta ruta está activa';
});
