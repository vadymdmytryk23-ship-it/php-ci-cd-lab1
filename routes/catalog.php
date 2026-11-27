<?php

use App\Http\Controllers\CabinetController;
use App\Http\Controllers\CatalogController;
use App\Http\Middleware\EnsureActiveUser;
use Illuminate\Support\Facades\Route;

Route::prefix('cabinet')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [CabinetController::class, 'login'])->middleware('throttle:8,1')->name('web.login');
    Route::view('/register', 'auth.register')->name('web.register');
    Route::post('/register', [CabinetController::class, 'register'])->middleware('throttle:8,1')->name('web.register.store');

    Route::middleware(['auth', EnsureActiveUser::class])->name('web.')->group(function (): void {
        Route::post('/logout', [CabinetController::class, 'logout'])->name('logout');
        Route::get('/profile', [CabinetController::class, 'profile'])->name('profile');
        Route::get('/statistics', [CabinetController::class, 'statistics'])->name('statistics');
        Route::pattern('id', '[0-9]+');
        foreach (array_keys(config('catalog')) as $key) {
            Route::resource($key, CatalogController::class)->parameters([$key => 'id']);
        }
        Route::post('/payments/{id}/confirm', [CatalogController::class, 'confirm'])->name('payments.confirm');
        Route::post('/payments/{id}/cancel', [CatalogController::class, 'cancel'])->name('payments.cancel');
        Route::get('/receipts/{id}/download', [CatalogController::class, 'download'])->name('receipts.download');
    });
});
