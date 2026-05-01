<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\EmployeeObserver;
use App\Models\Employee;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Employee::observe(EmployeeObserver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
