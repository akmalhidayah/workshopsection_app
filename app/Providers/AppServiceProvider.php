<?php

namespace App\Providers;

use App\Models\User;
use App\Models\SystemNotification;
use App\Observers\UserObserver;
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
 User::observe(UserObserver::class);

 view()->composer('layouts.admin', function ($view) {
    $view->with('adminNotifications', 
        SystemNotification::where('target_role','admin')
            ->latest()
            ->take(7)
            ->get()
    );

    $view->with('adminUnreadCount',
        SystemNotification::where('target_role','admin')
            ->where('is_read', false)
            ->count()
    );
});
    }
    
}
