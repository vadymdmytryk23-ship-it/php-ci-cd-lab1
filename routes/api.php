<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogApiController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\ReceiptDownloadController;
use App\Http\Middleware\EnsureActiveTokenUser;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthCheckController::class)->name('api.health');

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:8,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::pattern('record', '[0-9]+');

    $protected = ['auth:sanctum', EnsureActiveTokenUser::class];
    foreach (array_keys(config('catalog')) as $key) {
        $public = in_array($key, ['utility-accounts', 'service-providers', 'utility-services'], true);
        Route::apiResource($key, CatalogApiController::class)->parameters([$key => 'record'])
            ->except('update')
            ->middlewareFor(['store', 'destroy'], $protected)
            ->middlewareFor(['index', 'show'], $public ? [] : $protected);
        Route::put('/'.$key.'/{record}', [CatalogApiController::class, 'update'])->middleware($protected)->name($key.'.update');
    }
    Route::middleware($protected)->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/payments/{payment}/confirm', [CatalogApiController::class, 'confirm'])->whereNumber('payment');
        Route::post('/payments/{payment}/cancel', [CatalogApiController::class, 'cancel'])->whereNumber('payment');
        Route::get('/receipts/{receipt}/download', ReceiptDownloadController::class)->whereNumber('receipt');
        Route::get('/statistics', [CatalogApiController::class, 'statistics']);
    });
});
