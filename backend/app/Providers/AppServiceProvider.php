<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        // Map morph types for polymorphic relations
        Relation::enforceMorphMap([
            'user' => \App\Models\User::class,
            'product' => \App\Models\Product::class,
            'business' => \App\Models\Business::class,
        ]);
    }
}
