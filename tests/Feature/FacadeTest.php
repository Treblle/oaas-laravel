<?php

namespace Treblle\OaaS\Tests\Feature;

use Treblle\OaaS\Facades\TreblleOaaS;
use Treblle\OaaS\Filters\RequestFilters;
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