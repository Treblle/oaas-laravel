<?php

namespace Treblle\OaaS\Tests\Unit;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
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
        
        $this->client = new OaaSClient(
            apiToken: 'test-token',
            baseUrl: 'https://httpbin.org', // Use httpbin for testing
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
        // Test the data transformation without HTTP calls
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

        // Test that RequestCollection can be created from API response
        $collection = RequestCollection::fromApiResponse($apiResponse);

        // Assertions
        $this->assertInstanceOf(RequestCollection::class, $collection);
        $this->assertCount(1, $collection->getRequests());
        $this->assertEquals(1, $collection->total());
        $this->assertEquals('Requests retrieved successfully', $collection->getMessage());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->isNotEmpty());
    }

    public function test_filter_methods_return_correct_parameters(): void
    {
        $filters = $this->client->requests('workspace-123', 'api-456')
            ->whereCustomer('customer-123')
            ->whereMethod(HttpMethod::POST)
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

    public function test_exception_properties(): void
    {
        // Test OaaSException without HTTP calls
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('json')->andReturn(['error' => 'test error']);
        
        $exception = new OaaSException(
            message: 'Test error message',
            code: 422,
            response: $mockResponse
        );

        $this->assertEquals('Test error message', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
        $this->assertTrue($exception->hasResponse());
        $this->assertEquals(['error' => 'test error'], $exception->getResponseData());
    }

    public function test_exception_without_response(): void
    {
        $exception = new OaaSException(
            message: 'Network error',
            code: 0
        );

        $this->assertEquals('Network error', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertFalse($exception->hasResponse());
        $this->assertNull($exception->getResponseData());
    }

    public function test_get_request_details_from_api_response(): void
    {
        // Test RequestDetails creation from API response
        $requestResponse = [
            'data' => [
                'id' => '01JAV053YPYX62CB73DVE1ZQBM',
                'type' => 'Request',
                'attributes' => [
                    'path' => '/api/users/123',
                    'endpoint_path' => '/api/users/{id}',
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
                    'endpoint' => ['id' => 'endpoint-123', 'type' => 'Endpoint', 'attributes' => ['method' => 'GET', 'path' => '/api/users/{id}']],
                    'path_variables' => ['id' => '123'],
                    'request_data' => [],
                    'response_data' => ['user' => ['id' => 123, 'name' => 'John Doe']],
                    'request_url' => 'https://api.example.com/api/users/123',
                    'source' => 'Sdk',
                    'number_of_comments' => 0,
                    'log_file' => 'requests.log',
                    'metadata' => [],
                    'latitude' => 40.7128,
                    'longitude' => -74.0060,
                    'external_tag_id' => null,
                    'problem' => null,
                    'request' => ['headers' => ['Content-Type' => 'application/json'], 'body' => []],
                    'response' => ['headers' => ['Content-Type' => 'application/json'], 'body' => ['user' => ['id' => 123, 'name' => 'John Doe']]],
                    'server' => ['php_version' => '8.1', 'laravel_version' => '10.0'],
                    'compliance' => ['gdpr_compliant' => true],
                    'security_data' => ['threats_detected' => 0],
                ]
            ],
            'message' => 'Request retrieved successfully'
        ];

        // Test that RequestDetails can be created from API response
        $details = RequestDetails::fromApiResponse($requestResponse);

        // Assertions
        $this->assertInstanceOf(RequestDetails::class, $details);
    }
}