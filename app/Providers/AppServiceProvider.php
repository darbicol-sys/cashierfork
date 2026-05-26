<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
        // Share recent notifications with all views for authenticated users
        View::composer('*', function ($view) {
            $user = Auth::user();
            if (! $user) {
                $view->with('notif_data', []);
                return;
            }
            $notifications = $user->notifications()->latest()->take(20)->get();
            $notif_data = $notifications->map(function($n) {
                $d = $n->data ?? [];
                return [
                    'id' => $n->id,
                    'icon' => $d['icon'] ?? 'bi-bell',
                    'cls' => $d['cls'] ?? 'ni-gold',
                    'text' => $d['message'] ?? ($d['text'] ?? ''),
                    'time' => $d['time'] ?? ($n->created_at ? $n->created_at->diffForHumans() : ''),
                    'ts' => $n->created_at ? $n->created_at->toIso8601String() : null,
                    'unread' => $n->read_at ? false : true,
                    'data' => $d,
                ];
            })->toArray();
            $view->with('notif_data', $notif_data);
        });
    }
}
