<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseController;

Route::get('/request-code', [LicenseController::class, 'requestCode'])->name('license.request');
Route::get('/activate', [LicenseController::class, 'activateForm'])->name('license.activate.form');
Route::post('/activate', [LicenseController::class, 'activate'])->name('license.activate');

Route::get('/', [LicenseController::class, 'index'])->name('index');


Route::get('/h', function () {
    return view('welcome');
});
