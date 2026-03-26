<?php

namespace App\Providers;

use App\Models\Profile;
use App\Models\User;
use App\Observers\ProfileObserver;
use App\Observers\UserObserver;
use App\Repositories\Contracts\GraphRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;
use App\Repositories\Neo4jGraphRepository;
use App\Services\Contracts\PrivacyEngineInterface;
use App\Services\Contracts\SmartInvitationServiceInterface;
use App\Services\PrivacyEngineService;
use App\Services\SmartInvitationService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(GraphRepositoryInterface::class, Neo4jGraphRepository::class);
        $this->app->bind(PrivacyEngineInterface::class, PrivacyEngineService::class);
        $this->app->bind(SmartInvitationServiceInterface::class, SmartInvitationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Profile::observe(ProfileObserver::class);

        Vite::prefetch(concurrency: 3);
    }
}
