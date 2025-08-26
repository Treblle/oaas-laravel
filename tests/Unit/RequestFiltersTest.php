<?php

namespace Treblle\OaaS\Tests\Unit;

use Treblle\OaaS\Contracts\OaaSClientInterface;
use Treblle\OaaS\Enums\Device;
use Treblle\OaaS\Enums\HttpMethod;
use Treblle\OaaS\Enums\SortOrder;
use Treblle\OaaS\Enums\TimePeriod;
use Treblle\OaaS\Filters\RequestFilters;
use Treblle\OaaS\Tests\TestCase;
use Mockery;

class RequestFiltersTest extends TestCase
{
    private $mockClient;
    private RequestFilters $filters;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = Mockery::mock(OaaSClientInterface::class);
        $this->filters = new RequestFilters($this->mockClient, 'workspace-123', 'api-456');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_where_customer_sets_external_user_id_filter(): void
    {
        $this->filters->whereCustomer('customer-123');

        $expected = ['filter[external_user_id]' => 'customer-123'];
        $this->assertEquals($expected, $this->filters->toArray());
    }


    public function test_where_location_sets_location_filter(): void
    {
        $this->filters->whereLocation('New York, NY, US');

        $expected = ['filter[location]' => 'New York, NY, US'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_with_params_sets_params_filter(): void
    {
        $this->filters->withParams('search term');

        $expected = ['filter[params]' => 'search term'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_where_method_sets_method_filter(): void
    {
        $this->filters->whereMethod(HttpMethod::POST);

        $expected = ['filter[method]' => 'POST'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_where_device_sets_device_filter(): void
    {
        $this->filters->whereDevice(Device::IOS);

        $expected = ['filter[device]' => 'iOS'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_where_http_code_accepts_integer(): void
    {
        $this->filters->whereHttpCode(404);

        $expected = ['filter[http_code]' => 404];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_where_http_code_accepts_string_range(): void
    {
        $this->filters->whereHttpCode('4xx');

        $expected = ['filter[http_code]' => '4xx'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_where_successful_sets_2xx_filter(): void
    {
        $this->filters->whereSuccessful();

        $expected = ['filter[http_code]' => '2xx'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_where_client_error_sets_4xx_filter(): void
    {
        $this->filters->whereClientError();

        $expected = ['filter[http_code]' => '4xx'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_where_server_error_sets_5xx_filter(): void
    {
        $this->filters->whereServerError();

        $expected = ['filter[http_code]' => '5xx'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_where_time_period_sets_time_period_filter(): void
    {
        $this->filters->whereTimePeriod(TimePeriod::LAST_24_HOURS);

        $expected = ['filter[time_period]' => 'hour,24'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_with_problems_sets_has_problems_to_1(): void
    {
        $this->filters->withProblems();

        $expected = ['filter[has_problems]' => 1];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_without_problems_sets_has_problems_to_0(): void
    {
        $this->filters->withoutProblems();

        $expected = ['filter[has_problems]' => 0];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_sort_by_sets_sort_parameter(): void
    {
        $this->filters->sortBy(SortOrder::LOAD_TIME_DESC);

        $expected = ['sort' => '-load_time'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_sort_by_created_at_desc(): void
    {
        $this->filters->sortByCreatedAtDesc();

        $expected = ['sort' => '-created_at'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_sort_by_created_at_asc(): void
    {
        $this->filters->sortByCreatedAtAsc();

        $expected = ['sort' => 'created_at'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_sort_by_load_time_fastest(): void
    {
        $this->filters->sortByLoadTimeFastest();

        $expected = ['sort' => 'load_time'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_sort_by_load_time_slowest(): void
    {
        $this->filters->sortByLoadTimeSlowest();

        $expected = ['sort' => '-load_time'];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_limit_sets_limit_parameter(): void
    {
        $this->filters->limit(25);

        $expected = ['limit' => 25];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_limit_respects_maximum_limit(): void
    {
        config(['treblle-oaas.max_limit' => 50]);
        
        $this->filters->limit(100);

        $expected = ['limit' => 50];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_limit_enforces_minimum_limit(): void
    {
        $this->filters->limit(-5);

        $expected = ['limit' => 1];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_page_sets_page_parameter(): void
    {
        $this->filters->page(3);

        $expected = ['page' => 3];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_page_enforces_minimum_page(): void
    {
        $this->filters->page(-1);

        $expected = ['page' => 1];
        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_multiple_filters_can_be_chained(): void
    {
        $this->filters
            ->whereCustomer('customer-123')
            ->whereMethod(HttpMethod::POST)
            ->whereDevice(Device::IOS)
            ->whereSuccessful()
            ->withoutProblems()
            ->sortByLoadTimeSlowest()
            ->limit(15)
            ->page(2);

        $expected = [
            'filter[external_user_id]' => 'customer-123',
            'filter[method]' => 'POST',
            'filter[device]' => 'iOS',
            'filter[http_code]' => '2xx',
            'filter[has_problems]' => 0,
            'sort' => '-load_time',
            'limit' => 15,
            'page' => 2,
        ];

        $this->assertEquals($expected, $this->filters->toArray());
    }

    public function test_to_array_only_includes_set_parameters(): void
    {
        $this->filters->whereCustomer('customer-123');

        $result = $this->filters->toArray();

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('filter[external_user_id]', $result);
        $this->assertArrayNotHasKey('limit', $result);
        $this->assertArrayNotHasKey('page', $result);
        $this->assertArrayNotHasKey('sort', $result);
    }
}