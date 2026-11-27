<?php

declare(strict_types=1);

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

require __DIR__.'/catalog.php';

Route::get('/health', HealthCheckController::class)->name('health');
Route::get('/system-info', [HealthCheckController::class, 'info'])
    ->name('system-info');
