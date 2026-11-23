<?php

declare(strict_types=1);

use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\UtilityAccountController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/health', fn () => response()->json([
    'status' => 'OK',
    'timestamp' => now(),
]))->name('health');

Route::get('/system-info', [MeterReadingController::class, 'info'])
    ->name('system-info');

Route::prefix('utility-accounts')->name('utility-accounts.')->group(function (): void {
    Route::get('/', [UtilityAccountController::class, 'index'])->name('index');
    Route::post('/', [UtilityAccountController::class, 'store'])
        ->middleware('utility-account.access')
        ->name('store');
    Route::get('/{id}', [UtilityAccountController::class, 'show'])
        ->whereNumber('id')
        ->name('show');
});
