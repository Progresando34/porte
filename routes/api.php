<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DocumentoEmpresaController;

Route::get('/health', function (Request $request) {
    return 'Health ... active';
});


Route::prefix('documentos')->group(function () {

    Route::get('/existe/{doc}', [DocumentoEmpresaController::class, 'existe']);

    Route::post('/registrar', [DocumentoEmpresaController::class, 'registrar']);

});
