<?php

namespace App\Providers;

use App\Events\LeadConvertedToClient;
use App\Helpers\OptimizationHelper;
use App\Listeners\HandleLeadConvertedToClient;
use App\Models\Client;
use App\Models\Job;
use App\Models\Lead;
use App\Services\MailSettingsRegistrar;
use App\Support\CrmPermissions;
use App\Support\WebsiteSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        // Register domain event listeners in one central place.
        Event::listen(
            LeadConvertedToClient::class,
            HandleLeadConvertedToClient::class
        );

        Gate::define('view-dashboard', static fn ($user) => CrmPermissions::canViewDashboard($user));
        Gate::define('manage-leads', static fn ($user) => CrmPermissions::canManageLeads($user));
        Gate::define('manage-customers', static fn ($user) => CrmPermissions::canManageCustomers($user));
        Gate::define('view-jobs', static fn ($user) => CrmPermissions::canViewJobs($user));
        Gate::define('manage-job-records', static fn ($user) => CrmPermissions::canManageJobRecords($user));
        Gate::define('assign-jobs', static fn ($user) => CrmPermissions::canAssignJobs($user));
        Gate::define('manage-users', static fn ($user) => CrmPermissions::canManageUsers($user));
        Gate::define('manage-employee-bonuses', static fn ($user) => CrmPermissions::canManageEmployeeBonuses($user));
        Gate::define('manage-masters', static fn ($user) => CrmPermissions::canManageMasters($user));
        Gate::define('upload-job-images', static fn ($user) => CrmPermissions::canUploadJobImages($user));
        Gate::define('view-mower-report', static fn ($user) => CrmPermissions::canViewReport($user, CrmPermissions::VIEW_MOWER_REPORT));
        Gate::define('view-checklist-report', static fn ($user) => CrmPermissions::canViewReport($user, CrmPermissions::VIEW_CHECKLIST_REPORT));
        Gate::define('manage-discussions', static fn ($user) => CrmPermissions::canManageDiscussions($user));
        Gate::define('manage-contractors', static fn ($user) => CrmPermissions::canManageContractors($user));
        Gate::define('manage-followups', static fn ($user) => CrmPermissions::canManageFollowups($user));
        Gate::define('verify-jobs', static fn ($user) => CrmPermissions::canVerifyJobs($user));

        // Module 14: Invalidate cheap aggregate caches whenever CRM primitives change.
        $bumpCaches = static function (): void {
            OptimizationHelper::forgetDashboardStats();
            OptimizationHelper::bumpMapCacheGeneration();
        };

        Lead::saved($bumpCaches);
        Lead::deleted($bumpCaches);
        Lead::restored($bumpCaches);

        Client::saved($bumpCaches);
        Client::deleted($bumpCaches);
        Client::restored($bumpCaches);

        Job::saved($bumpCaches);
        Job::deleted($bumpCaches);
        Job::restored($bumpCaches);

        View::composer(['components.layouts.*', 'components.auth.*', 'auth.*'], function ($view): void {
            $view->with('websiteBranding', WebsiteSettings::branding());
        });

        View::composer(['components.layouts.dashboard', 'components.layouts.app', 'components.layouts.mower'], function ($view): void {
            if (! auth()->check()) {
                $view->with('unreadNotificationCount', 0);

                return;
            }

            $user = auth()->user();
            $ttlSeconds = (int) config('mowing.cache.ttl.notification_unread_seconds', 20);

            $count = Cache::remember(
                OptimizationHelper::notificationUnreadKey((int) $user->id),
                $ttlSeconds,
                fn (): int => (int) $user->unreadNotifications()->count()
            );

            $view->with('unreadNotificationCount', $count);
        });

        // Overlay SMTP / mailer values when Super Admin enables database-driven mail in Settings.
        MailSettingsRegistrar::applyFromDatabase();
    }
}
