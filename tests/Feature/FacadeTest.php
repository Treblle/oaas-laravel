<?php

namespace Treblle\OaaS\Tests\Feature;

use Treblle\OaaS\Contracts\OaaSClientInterface;
use Treblle\OaaS\Facades\TreblleOaaS;
use Treblle\OaaS\Filters\RequestFilters;
use Treblle\OaaS\OaaSServiceProvider;
use Treblle\OaaS\Tests\TestCase;

class FacadeTest extends TestCase
{
    public function test_facade_resolves_client_instance(): void
    {
        $this->assertTrue(class_exists(\Treblle\OaaS\Facades\TreblleOaaS::class));
    }

    public function test_facade_requests_method_returns_filters(): void
    {
        $filters = TreblleOaaS::requests('workspace-123', 'api-456');
        
        $this->assertInstanceOf(RequestFilters::class, $filters);
    }

    public function test_legacy_base_url_is_redirected_to_new_url(): void
    {
        config(['treblle-oaas.base_url' => 'https://api-forge.treblle.com/api/v1']);

        $this->app->forgetInstance(OaaSClientInterface::class);
        $this->app->forgetInstance('treblle-oaas');

        $provider = new OaaSServiceProvider($this->app);
        $provider->register();

        set_error_handler(function (int $errno, string $errstr): bool {
            $this->assertSame(E_USER_DEPRECATED, $errno);
            $this->assertStringContainsString('https://api.treblle.com/v1', $errstr);
            return true;
        });

        $client = $this->app->make(OaaSClientInterface::class);

        restore_error_handler();

        $this->assertInstanceOf(OaaSClientInterface::class, $client);
    }

    public function test_facade_can_chain_filter_methods(): void
    {
        $filters = TreblleOaaS::requests('workspace-123', 'api-456')
            ->whereCustomer('customer-123')
            ->limit(10);

        $expected = [
            'filter[external_user_id]' => 'customer-123',
            'limit' => 10,
        ];

        $this->assertEquals($expected, $filters->toArray());
    }
}