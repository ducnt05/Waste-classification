<?php

use App\Http\Controllers\WasteClassificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WasteClassificationController::class, 'index'])->name('home');
Route::post('/classify', [WasteClassificationController::class, 'store'])->name('waste-classifications.store');
Route::delete('/classify/{id}', [WasteClassificationController::class, 'destroy'])->name('waste-classifications.destroy');
