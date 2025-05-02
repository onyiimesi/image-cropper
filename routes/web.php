<?php

use App\Http\Controllers\ImageCropController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [ImageCropController::class, 'index']);
Route::post('/crop-image', [ImageCropController::class, 'uploadCropped'])->name('crop.upload');
