<?php

use App\Http\Controllers\PdfVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/verificar', [PdfVerificationController::class, 'showForm']);
Route::post('/verificar', [PdfVerificationController::class, 'verify']);
