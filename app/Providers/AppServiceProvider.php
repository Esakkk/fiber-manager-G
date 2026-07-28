<?php

namespace App\Providers;

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
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'pop' => \App\Models\Pop::class,
            'olt' => \App\Models\Olt::class,
            'pon' => \App\Models\Pon::class,
            'odc' => \App\Models\Odc::class,
            'odp' => \App\Models\Odp::class,
        ]);
    }
}
