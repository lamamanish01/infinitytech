<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Schema;
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

        try {

            if (Schema::hasTable('activity_logs')) {
                $view->with('activities', ActivityLog::latest()->take(10)->get());
                $view->with('unreadCount', ActivityLog::where('is_read', false)->count());
            } else {
                $view->with('activities', collect());
                $view->with('unreadCount', 0);
            }

        } catch (\Throwable $e) {
            $view->with('activities', collect());
            $view->with('unreadCount', 0);
        }
    });
    }
}
