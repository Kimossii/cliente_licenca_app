<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseController;


Route::middleware('license.check')->group(function () {
    Route::get('/request-code', [LicenseController::class, 'requestCode'])->name('license.request');
    Route::get('/activate', [LicenseController::class, 'activateForm'])->name('license.activate.form');
    Route::post('/activate', [LicenseController::class, 'activate'])->name('license.activate');
    Route::get('/import/upload-key', [LicenseController::class, 'formKeyPublic'])->name('import.uploadKey');
    Route::post('/uploadkey', [LicenseController::class, 'uploadKey'])->name('client.uploadKey');

    //Index aqui
    Route::get('/', [LicenseController::class, 'index'])->name('index')->middleware('license.check');

});








Route::get('/H', function () {
    return view('welcome');
});
