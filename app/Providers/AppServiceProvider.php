<?php

namespace App\Providers;

use App\Models\Hotel;
use App\Policies\HotelPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /*
    protected $policies = [
        Hotel::class => HotelPolicy::class,
    ]; */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
