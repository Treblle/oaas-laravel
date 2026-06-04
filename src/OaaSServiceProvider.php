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
                baseUrl: $this->resolveBaseUrl(config('treblle-oaas.base_url')),
                timeout: config('treblle-oaas.timeout'),
                connectTimeout: config('treblle-oaas.connect_timeout')
            );
        });

        $this->app->alias(OaaSClientInterface::class, 'treblle-oaas');
    }

    private function resolveBaseUrl(string $url): string
    {
        if ($url === 'https://api-forge.treblle.com/api/v1') {
            trigger_error(
                'The Treblle OaaS base URL has changed. Please update your published config or TREBLLE_OAAS_BASE_URL to https://api.treblle.com/v1',
                E_USER_DEPRECATED
            );
            return 'https://api.treblle.com/v1';
        }

        return $url;
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