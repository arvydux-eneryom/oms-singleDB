<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\User;
use App\Policies\CustomerPolicy;
use App\Policies\SubdomainPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Customer::class => CustomerPolicy::class,
        User::class => UserPolicy::class,
        Domain::class => SubdomainPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service bindings
        $this->app->bind(
            \App\Services\SmsServiceInterface::class,
            \App\Services\TwilioSmsService::class
        );

        // Repository bindings
        $this->app->bind(
            \App\Contracts\Repositories\SmsMessageRepositoryInterface::class,
            \App\Repositories\SmsMessageRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\SentSmsQuestionRepositoryInterface::class,
            \App\Repositories\SentSmsQuestionRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\SmsResponseRepositoryInterface::class,
            \App\Repositories\SmsResponseRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
