<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\SiteSetting;
use App\Models\TimerAnnouncement;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            $siteSettings = Schema::hasTable('site_settings') ? SiteSetting::allMap() : [];
            $liveTimers = Schema::hasTable('timer_announcements')
                ? TimerAnnouncement::live()->take(3)->get()
                : collect();
            $urgentAlerts = Schema::hasTable('announcements')
                ? Announcement::where('is_published', true)
                    ->where('is_urgent', true)
                    ->latest('published_at')
                    ->take(3)
                    ->get()
                : collect();

            $shared = [
                'siteSettings' => $siteSettings,
                'liveTimers' => $liveTimers,
                'urgentAlerts' => $urgentAlerts,
            ];

            $user = Auth::user();
            $unreadCount = 0;
            $unreadUrgentCount = 0;

            if ($user && Schema::hasTable('announcements') && Schema::hasTable('announcement_reads')) {
                $unreadCount = $user->unreadAnnouncementsCount();
                $unreadUrgentCount = $user->unreadUrgentCount();
            } elseif (Schema::hasTable('announcements')) {
                $unreadCount = Announcement::where('is_published', true)
                    ->where('published_at', '>=', now()->subDays(14))
                    ->count();
                $unreadUrgentCount = $shared['urgentAlerts']->count();
            }

            $view->with($shared)
                ->with('unreadNotificationCount', $unreadCount)
                ->with('unreadUrgentCount', $unreadUrgentCount);
        });
    }
}
