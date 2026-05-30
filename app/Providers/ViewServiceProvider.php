<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $activities = ActivityLog::latest()->take(10)->get();

            $unreadCount = ActivityLog::where('is_read', false)->count();

            $view->with([
                'activities' => $activities,
                'unreadCount' => $unreadCount
            ]);
        });
    }
}
