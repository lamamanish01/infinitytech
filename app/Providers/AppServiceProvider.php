<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupGates();
        $this->setupPagination();
        // $this->shareGlobalViewData();
    }

    /**
     * Handle role-based access control
     */
    private function setupGates(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }

    /**
     * Bootstrap pagination styling
     */
    private function setupPagination(): void
    {
        Paginator::useBootstrap();
    }

    /**
     * Share global data with all views
     */
    // private function shareGlobalViewData(): void
    // {
    //     View::share([
    //         'activities' => ActivityLog::latest()->take(10)->get(),
    //         'unreadCount' => ActivityLog::where('is_read', false)->count(),
    //     ]);
    // }
}
