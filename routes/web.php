<?php

declare(strict_types=1);

use App\Http\Controllers\MeterReadingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

require __DIR__.'/catalog.php';

Route::get('/health', fn () => response()->json([
    'status' => 'OK',
    'timestamp' => now(),
]))->name('health');

Route::get('/system-info', [MeterReadingController::class, 'info'])
    ->name('system-info');
