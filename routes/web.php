<?php

declare(strict_types=1);

use App\Http\Controllers\BillController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UtilityAccountController;
use App\Http\Controllers\UtilityServiceController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

require __DIR__.'/catalog.php';

Route::get('/health', fn () => response()->json([
    'status' => 'OK',
    'timestamp' => now(),
]))->name('health');

Route::get('/system-info', [MeterReadingController::class, 'info'])
    ->name('system-info');

Route::pattern('id', '[0-9]+');

Route::prefix('utility-accounts')->name('utility-accounts.')->group(function (): void {
    Route::get('/', [UtilityAccountController::class, 'index'])->name('index');
    Route::post('/', [UtilityAccountController::class, 'store'])
        ->middleware('utility-account.access')
        ->name('store');
    Route::get('/{id}', [UtilityAccountController::class, 'show'])
        ->whereNumber('id')
        ->name('show');
    Route::middleware('utility-account.access')->group(function (): void {
        Route::patch('/{id}', [UtilityAccountController::class, 'update'])->name('update');
        Route::post('/{id}/meter-readings', [MeterReadingController::class, 'store']);
        Route::post('/{id}/bills', [BillController::class, 'store']);
    });
});

Route::prefix('service-providers')->group(function (): void {
    Route::get('/', [ServiceProviderController::class, 'index']);
    Route::middleware('utility-account.access')->group(function (): void {
        Route::post('/', [ServiceProviderController::class, 'store']);
        Route::post('/{id}/services', [UtilityServiceController::class, 'store']);
    });
});

Route::prefix('utility-services')->group(function (): void {
    Route::get('/', [UtilityServiceController::class, 'index']);
    Route::patch('/{id}/tariff', [UtilityServiceController::class, 'update'])->middleware('utility-account.access');
});

Route::prefix('bills')->group(function (): void {
    Route::get('/', [BillController::class, 'index']);
    Route::post('/{id}/payments', [PaymentController::class, 'store'])->middleware('utility-account.access');
});

Route::prefix('payments')->group(function (): void {
    Route::get('/', [PaymentController::class, 'index']);
    Route::middleware('utility-account.access')->group(function (): void {
        Route::post('/{id}/confirm', [PaymentController::class, 'confirm']);
        Route::post('/{id}/cancel', [PaymentController::class, 'cancel']);
    });
});

Route::prefix('receipts')->group(function (): void {
    Route::get('/', [ReceiptController::class, 'index']);
    Route::get('/{id}/download', [ReceiptController::class, 'download']);
});

Route::get('/meter-readings', [MeterReadingController::class, 'index']);

Route::prefix('users')->group(function (): void {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/', [UserController::class, 'store']);
    Route::middleware('auth')->group(function (): void {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/logout', [UserController::class, 'logout']);
    });
    Route::middleware('utility-account.access')->group(function (): void {
        Route::get('/', [UserController::class, 'index']);
        Route::patch('/{id}/block', [UserController::class, 'block']);
        Route::get('/statistics', [UserController::class, 'statistics']);
    });
});
