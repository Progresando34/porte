<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\DocumentoEmpresaController;

Route::prefix('documentos')->group(function () {

    Route::get('/existe/{doc}', [DocumentoEmpresaController::class, 'existe']);

    Route::post('/registrar', [DocumentoEmpresaController::class, 'registrar']);

});
