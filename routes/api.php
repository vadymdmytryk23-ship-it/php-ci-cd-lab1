<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Middleware\EnsureActiveTokenUser;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:8,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::pattern('id', '[0-9]+');

    $protected = ['auth:sanctum', EnsureActiveTokenUser::class];
    foreach (array_keys(config('catalog')) as $key) {
        $public = in_array($key, ['utility-accounts', 'service-providers', 'utility-services'], true);
        Route::apiResource($key, CatalogApiController::class)->parameters([$key => 'id'])
            ->except('update')
            ->middlewareFor(['store', 'destroy'], $protected)
            ->middlewareFor(['index', 'show'], $public ? [] : $protected);
        Route::put('/'.$key.'/{id}', [CatalogApiController::class, 'update'])->middleware($protected)->name($key.'.update');
    }
    Route::middleware($protected)->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/payments/{id}/confirm', [CatalogApiController::class, 'confirm']);
        Route::post('/payments/{id}/cancel', [CatalogApiController::class, 'cancel']);
        Route::get('/receipts/{id}/download', [CatalogApiController::class, 'download']);
        Route::get('/statistics', [CatalogApiController::class, 'statistics']);
    });
});
