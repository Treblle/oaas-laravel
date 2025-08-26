<?php

namespace Treblle\OaaS\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Treblle\OaaS\OaaSServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'treblle-oaas.api_token' => 'test-token',
            'treblle-oaas.base_url' => 'https://api-test.treblle.com/api/v1',
            'treblle-oaas.timeout' => 30,
            'treblle-oaas.connect_timeout' => 10,
            'treblle-oaas.default_limit' => 20,
            'treblle-oaas.max_limit' => 50,
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            OaaSServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'TreblleOaaS' => \Treblle\OaaS\Facades\TreblleOaaS::class,
        ];
    }
}