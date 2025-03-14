<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIDetectorController;

Route::get('/', [AIDetectorController::class, 'index'])->name('detector.index');
Route::post('/ai-detector/analyze', [AIDetectorController::class, 'analyze'])->name('detector.analyze');
