<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoEmpresaController;

Route::prefix('documentos')->group(function () {

    Route::post('/existe', [DocumentoEmpresaController::class, 'existe']);

    Route::post('/registrar', [DocumentoEmpresaController::class, 'registrar']);

});
