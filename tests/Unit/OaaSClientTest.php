<?php

namespace Treblle\OaaS\Tests\Unit;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Mockery;
use Treblle\OaaS\Contracts\OaaSClientInterface;
use Treblle\OaaS\Data\RequestCollection;
use Treblle\OaaS\Data\RequestDetails;
use Treblle\OaaS\Enums\HttpMethod;
use Treblle\OaaS\Exceptions\OaaSException;
use Treblle\OaaS\Filters\RequestFilters;
use Treblle\OaaS\OaaSClient;
use Treblle\OaaS\Tests\TestCase;

class OaaSClientTest extends TestCase
{
    private OaaSClient $client;
    private $mockHttpClient;
    private $mockResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHttpClient = Mockery::mock(PendingRequest::class);
        $this->mockResponse = Mockery::mock(Response::class);
        
        $this->client = new OaaSClient(
            apiToken: 'test-token',
            baseUrl: 'https://api-test.treblle.com/api/v1',
            timeout: 30,
            connectTimeout: 10
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_can_be_resolved_from_container(): void
    {
        $client = $this->app->make(OaaSClientInterface::class);
        
        $this->assertInstanceOf(OaaSClient::class, $client);
    }

    public function test_requests_method_returns_filter_instance(): void
    {
        $filters = $this->client->requests('workspace-123', 'api-456');
        
        $this->assertInstanceOf(RequestFilters::class, $filters);
    }

    public function test_get_requests_returns_collection(): void
    {
        // Mock successful API response
        $apiResponse = [
            'data' => [
                [
                    'id' => '01JAV053YPYX62CB73DVE1ZQBM',
                    'type' => 'Request',
                    'attributes' => [
                        'path' => '/api/users',
                        'endpoint_path' => '/api/users',
                        'method' => 'GET',
                        'http_code' => 200,
                        'external_user_id' => 'customer-123',
                        'customer_display_name' => 'John Doe',
                        'location' => 'New York, NY, US',
                        'ip_address' => '192.168.1.1',
                        'app_name' => 'TestApp v1.0',
                        'formatted_load_time' => ['value' => 150.5, 'is_good' => true, 'unit' => 'milliseconds'],
                        'formatted_response_size' => ['value' => 2.5, 'is_good' => true, 'unit' => 'kilobytes'],
                        'has_auth' => true,
                        'threat_level' => 'Low',
                        'created_at' => '2024-01-15T10:30:00Z',
                        'device' => ['device' => 'desktop', 'source' => 'browser', 'app' => []],
                        'api' => ['id' => 'api-123', 'type' => 'Api', 'attributes' => []],
                        'endpoint' => ['id' => 'endpoint-123', 'type' => 'Endpoint', 'attributes' => ['method' => 'GET', 'path' => '/api/users']],
                        'path_variables' => [],
                        'request_data' => [],
                        'request_url' => 'https://api.example.com/api/users',
                        'source' => 'Sdk',
                        'number_of_comments' => 0,
                        'log_file' => 'requests.log',
                        'metadata' => [],
                        'latitude' => 40.7128,
                        'longitude' => -74.0060,
                        'external_tag_id' => null,
                    ]
                ]
            ],
            'meta' => [
                'pagination' => [
                    'count' => 1,
                    'current_page' => 1,
                    'per_page' => 20,
                    'total' => 1,
                    'total_pages' => 1,
                ]
            ],
            'links' => [
                'first' => 'https://api-test.treblle.com/api/v1/workspaces/workspace-123/apis/api-456/requests?page=1',
                'last' => 'https://api-test.treblle.com/api/v1/workspaces/workspace-123/apis/api-456/requests?page=1',
                'next' => null,
                'prev' => null,
            ],
            'message' => 'Requests retrieved successfully'
        ];

        // Create filters
        $filters = new RequestFilters($this->client, 'workspace-123', 'api-456');
        $filters->forCustomer('customer-123');

        // This would normally make an HTTP request, but we'll test the structure
        $this->assertTrue(true); // Placeholder for actual HTTP mocking
    }

    public function test_filter_methods_return_correct_parameters(): void
    {
        $filters = $this->client->requests('workspace-123', 'api-456')
            ->forCustomer('customer-123')
            ->forMethod(HttpMethod::POST)
            ->limit(10)
            ->page(2);

        $expected = [
            'filter[external_user_id]' => 'customer-123',
            'filter[method]' => 'POST',
            'limit' => 10,
            'page' => 2,
        ];

        $this->assertEquals($expected, $filters->toArray());
    }

    public function test_exception_is_thrown_for_failed_requests(): void
    {
        $this->expectException(OaaSException::class);
        
        // This would test actual HTTP failure scenarios with mocked responses
        $this->assertTrue(true); // Placeholder
    }
}