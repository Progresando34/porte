<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\api\DocumentoEmpresaController;

Route::get('/health', function (Request $request) {
    return 'Health ... active';
});

Route::prefix('documentos')->middleware('sync.token')->group(function () {
    Route::get('/existe/{doc}', [DocumentoEmpresaController::class, 'existe']);
    Route::post('/registrar', [DocumentoEmpresaController::class, 'registrar']);
});
