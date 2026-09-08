<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\User;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        Horizon::routeMailNotificationsTo(config('platform.operations_email'));
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define(
            'viewHorizon',
            fn (?User $user): bool => $user?->can(UserPermission::DashboardView->value) === true,
        );
    }
}
