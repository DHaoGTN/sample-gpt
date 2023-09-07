<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Interfaces\UserRepositoryInterface::class, \App\Infrastructure\Repositories\UserRepository::class);
        $this->app->bind(\App\Interfaces\UsageRepositoryInterface::class, \App\Infrastructure\Repositories\UsageRepository::class);
        $this->app->bind(\App\Interfaces\InquiryRepositoryInterface::class, \App\Infrastructure\Repositories\InquiryRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
