<?php

use App\Http\Controllers\ReceiptDownloadController;
use App\Http\Controllers\Web\CabinetController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\SentryDemoController;
use App\Http\Middleware\EnsureActiveUser;
use Illuminate\Support\Facades\Route;

Route::prefix('cabinet')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::view('/login', 'auth.login')->name('login');
        Route::post('/login', [CabinetController::class, 'login'])->middleware('throttle:8,1')->name('web.login');
        Route::view('/register', 'auth.register')->name('web.register');
        Route::post('/register', [CabinetController::class, 'register'])->middleware('throttle:8,1')->name('web.register.store');
    });

    Route::pattern('record', '[0-9]+');
    Route::name('web.')->group(function (): void {
        Route::resource('utility-accounts', CatalogController::class)->parameters(['utility-accounts' => 'record'])->only(['index', 'show']);
    });

    Route::middleware(['auth', EnsureActiveUser::class])->name('web.')->group(function (): void {
        Route::post('/logout', [CabinetController::class, 'logout'])->name('logout');
        Route::get('/profile', [CabinetController::class, 'profile'])->name('profile');
        Route::get('/statistics', [CabinetController::class, 'statistics'])->name('statistics');
        foreach (array_keys(config('catalog')) as $key) {
            $resource = Route::resource($key, CatalogController::class)->parameters([$key => 'record']);
            if ($key === 'utility-accounts') {
                $resource->except(['index', 'show']);
            }
        }
        Route::post('/payments/{payment}/confirm', [CatalogController::class, 'confirm'])->whereNumber('payment')->name('payments.confirm');
        Route::post('/payments/{payment}/cancel', [CatalogController::class, 'cancel'])->whereNumber('payment')->name('payments.cancel');
        Route::get('/receipts/{receipt}/download', ReceiptDownloadController::class)->whereNumber('receipt')->name('receipts.download');
        Route::post('/sentry-demo', SentryDemoController::class)->middleware('throttle:3,1')->name('sentry-demo');
    });
});
