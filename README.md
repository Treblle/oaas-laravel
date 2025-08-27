# Treblle - API Intelligence Platform

[![Treblle API Intelligence](https://github.com/user-attachments/assets/b268ae9e-7c8a-4ade-95da-b4ac6fce6eea)](https://treblle.com)

[Website](http://treblle.com/) • [Documentation](https://docs.treblle.com/) • [Pricing](https://treblle.com/pricing)

Treblle is an API intelligence platfom that helps developers, teams and organizations understand their APIs from a single integration point.

---

## Treblle Laravel OaaS SDK

A Laravel SDK for Treblle Observability as a Service (OaaS). Easily retrieve and filter requests from APIs monitored by Treblle and expose that data to your end-customers.

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Guzzle HTTP 7.0 or higher

## Installation

Install the package via Composer:

```bash
composer require treblle/oaas-laravel
```

### Laravel Setup

The package will automatically register its service provider. Publish the configuration file:

```bash
php artisan vendor:publish --tag=treblle-oaas-config
```

### Configuration

Add your Treblle OaaS API token to your `.env` file:

```env
TREBLLE_OAAS_API_TOKEN=your_api_token_here
```

You can obtain your API token from the [Treblle Identity Dashboard](https://identity.treblle.com/developer-settings) under Developer Settings.

## Usage

### Basic Usage

To use the SDK you will need to pass your Workspace ID (found in Workspace Settings on the Treblle Dashboard) and your API ID (found in API Settings on the Treblle Dashboard)

```php
use Treblle\OaaS\Facades\TreblleOaaS;

// Get all requests for a customer
$requests = TreblleOaaS::requests('workspace-id', 'api-id')
    ->whereCustomer('customer-123')
    ->get();

foreach ($requests as $request) {
    echo "Request: {$request->getMethod()} {$request->getPath()}" . PHP_EOL;
    echo "Status: {$request->getHttpCode()}" . PHP_EOL;
    echo "Load Time: {$request->getLoadTimeMs()}ms" . PHP_EOL;
}
```

### Advanced Filtering

The SDK provides a fluent API for filtering requests:

```php
use Treblle\OaaS\Enums\HttpMethod;
use Treblle\OaaS\Enums\Device;
use Treblle\OaaS\Enums\TimePeriod;

$requests = TreblleOaaS::requests('workspace-id', 'api-id')
    ->whereCustomer('customer-123')
    ->whereMethod(HttpMethod::POST)
    ->whereDevice(Device::IOS)
    ->whereTimePeriod(TimePeriod::LAST_24_HOURS)
    ->whereSuccessful() // 2xx status codes
    ->withoutProblems()
    ->sortByLoadTimeSlowest()
    ->limit(10)
    ->get();
```

### Pagination

```php
// Get paginated results
$page1 = TreblleOaaS::requests('workspace-id', 'api-id')
    ->whereCustomer('customer-123')
    ->paginate(perPage: 20, page: 1);

echo "Total requests: {$page1->total()}" . PHP_EOL;
echo "Current page: {$page1->currentPage()} / {$page1->totalPages()}" . PHP_EOL;

// Check if there are more pages
if ($page1->hasNextPage()) {
    $page2 = TreblleOaaS::requests('workspace-id', 'api-id')
        ->whereCustomer('customer-123')
        ->paginate(perPage: 20, page: 2);
}
```

### Request Details

Get detailed information about a specific request:

```php
$requestDetails = TreblleOaaS::getRequest('workspace-id', 'api-id', 'request-id');

echo "Request URL: {$requestDetails->getRequestUrl()}" . PHP_EOL;
echo "Response Body: " . json_encode($requestDetails->getResponseBody()) . PHP_EOL;
echo "Server Info: " . json_encode($requestDetails->getServerInfo()) . PHP_EOL;

// Check compliance and security
if ($requestDetails->hasProblem()) {
    echo "Request has problems: " . json_encode($requestDetails->getProblem()) . PHP_EOL;
}

$compliance = $requestDetails->getComplianceReport();
echo "Compliance Status: {$compliance['status']}" . PHP_EOL;
echo "Compliance Score: {$compliance['overall_percentage']}%" . PHP_EOL;
```

### Available Filters

| Filter Method | Description |
|--------------|-------------|
| `whereCustomer(string $customerId)` | Filter by external user/customer ID |
| `whereLocation(string $location)` | Filter by geographic location |
| `withParams(string $params)` | Filter by request parameters |
| `whereMethod(HttpMethod $method)` | Filter by HTTP method |
| `whereDevice(Device $device)` | Filter by device type (iOS/Android/Desktop) |
| `whereHttpCode(int\|string $code)` | Filter by HTTP status code |
| `whereSuccessful()` | Filter for 2xx responses |
| `whereClientError()` | Filter for 4xx responses |
| `whereServerError()` | Filter for 5xx responses |
| `whereTimePeriod(TimePeriod $period)` | Filter by time period |
| `withProblems()` | Only requests with problems |
| `withoutProblems()` | Only requests without problems |

### Sorting Options

| Sort Method | Description |
|-------------|-------------|
| `sortByCreatedAtDesc()` | Most recent first (default) |
| `sortByCreatedAtAsc()` | Oldest first |
| `sortByLoadTimeFastest()` | Fastest requests first |
| `sortByLoadTimeSlowest()` | Slowest requests first |

### Helper Methods

Request summary objects provide helpful methods:

```php
foreach ($requests as $request) {
    // Status checks
    if ($request->isSuccessful()) {
        echo "✅ Successful request" . PHP_EOL;
    } elseif ($request->hasClientError()) {
        echo "⚠️ Client error: {$request->getHttpCode()}" . PHP_EOL;
    } elseif ($request->hasServerError()) {
        echo "❌ Server error: {$request->getHttpCode()}" . PHP_EOL;
    }
    
    // Performance checks
    if ($request->isLoadTimeFast()) {
        echo "⚡ Fast response: {$request->getLoadTimeMs()}ms" . PHP_EOL;
    } else {
        echo "🐌 Slow response: {$request->getLoadTimeMs()}ms" . PHP_EOL;
    }
    
    // Customer info
    if ($displayName = $request->getCustomerDisplayName()) {
        echo "Customer: {$displayName}" . PHP_EOL;
    }
    
    // Security info
    echo "Threat Level: {$request->getThreatLevel()}" . PHP_EOL;
    echo "Has Auth: " . ($request->hasAuth() ? 'Yes' : 'No') . PHP_EOL;
}
```

## Configuration Options

The configuration file allows you to customize various aspects:

```php
return [
    // API Configuration
    'api_token' => env('TREBLLE_OAAS_API_TOKEN'),
    'base_url' => env('TREBLLE_OAAS_BASE_URL', 'https://api-forge.treblle.com/api/v1'),
    
    // Request Timeouts
    'timeout' => env('TREBLLE_OAAS_TIMEOUT', 30),
    'connect_timeout' => env('TREBLLE_OAAS_CONNECT_TIMEOUT', 10),
    
    // Pagination Defaults
    'default_limit' => env('TREBLLE_OAAS_DEFAULT_LIMIT', 20),
    'max_limit' => env('TREBLLE_OAAS_MAX_LIMIT', 50),
];
```

## Error Handling

The SDK throws `OaaSException` for API errors:

```php
use Treblle\OaaS\Exceptions\OaaSException;

try {
    $requests = TreblleOaaS::requests('workspace-id', 'api-id')
        ->whereCustomer('customer-123')
        ->get();
} catch (OaaSException $e) {
    echo "API Error: {$e->getMessage()}" . PHP_EOL;
    echo "HTTP Status: {$e->getCode()}" . PHP_EOL;
    
    if ($e->hasResponse()) {
        $responseData = $e->getResponseData();
        echo "Response: " . json_encode($responseData) . PHP_EOL;
    }
}
```

## Data Models

The SDK provides rich data models:

- `RequestCollection` - Collection of request summaries with pagination
- `RequestSummary` - Summary information about a request
- `RequestDetails` - Complete request details including headers, body, compliance, etc.
- `PaginationMeta` - Pagination metadata
- `PaginationLinks` - Pagination navigation links

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For support, please visit [Treblle's documentation](https://docs.treblle.com) or contact [support@treblle.com](mailto:support@treblle.com).