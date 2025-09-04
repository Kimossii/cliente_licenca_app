<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
Route::get('/cliente/gerar-codigo', [ClientController::class, 'showForm']);
Route::post('/cliente/gerar-codigo', [ClientController::class, 'generateFingerprint']);

Route::get('/cliente/validar-licenca', [ClientController::class, 'showFormValidar']);

Route::post('/license/validate', [ClientController::class, 'validateLicense'])->name('license.validate');




Route::get('/', function () {
    return view('welcome');
});
