<?php

namespace App\Providers;

use App\Models\UtilityAccount;
use App\Policies\CatalogPolicy;
use App\Policies\UtilityAccountPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (config('catalog') as $entry) {
            $model = $entry['model'];
            Gate::policy($model, $model === UtilityAccount::class ? UtilityAccountPolicy::class : CatalogPolicy::class);
        }
    }
}
