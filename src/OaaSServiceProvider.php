<?php

namespace Treblle\OaaS;

use Illuminate\Support\ServiceProvider;
use Treblle\OaaS\Contracts\OaaSClientInterface;

class OaaSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/treblle-oaas.php', 'treblle-oaas');

        $this->app->singleton(OaaSClientInterface::class, function ($app) {
            return new OaaSClient(
                apiToken: config('treblle-oaas.api_token'),
                baseUrl: config('treblle-oaas.base_url'),
                timeout: config('treblle-oaas.timeout'),
                connectTimeout: config('treblle-oaas.connect_timeout')
            );
        });

        $this->app->alias(OaaSClientInterface::class, 'treblle-oaas');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/treblle-oaas.php' => config_path('treblle-oaas.php'),
            ], 'treblle-oaas-config');
        }
    }

    public function provides(): array
    {
        return [
            OaaSClientInterface::class,
            'treblle-oaas',
        ];
    }
}