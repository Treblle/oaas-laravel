<?php

/**
 * Basic Usage Examples for Treblle Laravel OaaS SDK
 * 
 * This file demonstrates the most common use cases for the SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Treblle\OaaS\Facades\TreblleOaaS;
use Treblle\OaaS\Enums\HttpMethod;
use Treblle\OaaS\Enums\Device;
use Treblle\OaaS\Enums\TimePeriod;
use Treblle\OaaS\Exceptions\OaaSException;

// Your Treblle workspace and API identifiers
$workspaceId = 'your-workspace-id';
$apiId = 'your-api-id';
$customerId = 'customer-123';

echo "=== Treblle OaaS SDK Examples ===" . PHP_EOL . PHP_EOL;

try {
    // Example 1: Basic customer request retrieval
    echo "1. Getting all requests for a customer..." . PHP_EOL;
    $requests = TreblleOaaS::requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->limit(5)
        ->get();
    
    echo "Found {$requests->count()} requests out of {$requests->total()} total." . PHP_EOL;
    
    foreach ($requests as $request) {
        echo "  - {$request->getMethod()} {$request->getPath()} [{$request->getHttpCode()}] - {$request->getLoadTimeMs()}ms" . PHP_EOL;
    }
    echo PHP_EOL;

    // Example 2: Filter by specific conditions
    echo "2. Getting POST requests from mobile devices in the last 24 hours..." . PHP_EOL;
    $mobileRequests = TreblleOaaS::requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->whereMethod(HttpMethod::POST)
        ->whereDevice(Device::IOS)
        ->whereTimePeriod(TimePeriod::LAST_24_HOURS)
        ->sortByLoadTimeSlowest()
        ->get();
    
    echo "Found {$mobileRequests->count()} mobile POST requests." . PHP_EOL . PHP_EOL;

    // Example 3: Error analysis
    echo "3. Analyzing error requests..." . PHP_EOL;
    $errorRequests = TreblleOaaS::requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->whereClientError()
        ->withProblems()
        ->limit(3)
        ->get();
    
    foreach ($errorRequests as $request) {
        echo "  - ERROR: {$request->getMethod()} {$request->getPath()} [{$request->getHttpCode()}]" . PHP_EOL;
        echo "    Location: {$request->getLocation()}" . PHP_EOL;
        echo "    Threat Level: {$request->getThreatLevel()}" . PHP_EOL;
        echo "    Auth: " . ($request->hasAuth() ? 'Yes' : 'No') . PHP_EOL;
    }
    echo PHP_EOL;

    // Example 4: Performance analysis
    echo "4. Performance analysis..." . PHP_EOL;
    $slowRequests = TreblleOaaS::requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->whereSuccessful()
        ->sortByLoadTimeSlowest()
        ->limit(3)
        ->get();
    
    foreach ($slowRequests as $request) {
        $performance = $request->isLoadTimeFast() ? '⚡ Fast' : '🐌 Slow';
        echo "  - {$performance}: {$request->getPath()} - {$request->getLoadTimeMs()}ms" . PHP_EOL;
        echo "    Response Size: {$request->getResponseSizeKb()}KB" . PHP_EOL;
    }
    echo PHP_EOL;

    // Example 5: Pagination
    echo "5. Paginated results..." . PHP_EOL;
    $paginatedRequests = TreblleOaaS::requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->paginate(perPage: 2, page: 1);
    
    echo "Page {$paginatedRequests->currentPage()} of {$paginatedRequests->totalPages()}" . PHP_EOL;
    echo "Showing {$paginatedRequests->count()} of {$paginatedRequests->total()} total requests" . PHP_EOL;
    
    if ($paginatedRequests->hasNextPage()) {
        echo "Has next page: Yes" . PHP_EOL;
    }
    echo PHP_EOL;

    // Example 6: Request details
    echo "6. Getting detailed request information..." . PHP_EOL;
    if ($requests->isNotEmpty()) {
        $firstRequest = $requests->first();
        $details = TreblleOaaS::getRequest($workspaceId, $apiId, $firstRequest->getId());
        
        echo "Request Details for: {$details->getMethod()} {$details->getPath()}" . PHP_EOL;
        echo "  Status: {$details->getHttpCode()}" . PHP_EOL;
        echo "  Customer: {$details->getCustomerDisplayName()}" . PHP_EOL;
        echo "  Location: {$details->getLocation()}" . PHP_EOL;
        
        // Server information
        $serverInfo = $details->getServerInfo();
        echo "  Server: {$serverInfo['software']} on {$serverInfo['name']}" . PHP_EOL;
        
        // Compliance information
        $compliance = $details->getComplianceReport();
        echo "  Compliance: {$compliance['status']} ({$compliance['overall_percentage']}%)" . PHP_EOL;
        
        // Security audit
        $securityData = $details->getSecurityAudit();
        if (!empty($securityData)) {
            echo "  Security Issues: " . count($securityData) . PHP_EOL;
        }
        
        if ($details->hasProblem()) {
            echo "  ⚠️ This request has problems!" . PHP_EOL;
        }
    }
    echo PHP_EOL;

    // Example 7: Location-based filtering
    echo "7. Filtering by location..." . PHP_EOL;
    $locationRequests = TreblleOaaS::requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->whereLocation('New York, New York, United States')
        ->limit(3)
        ->get();
    
    echo "Found {$locationRequests->count()} requests from New York" . PHP_EOL;
    echo PHP_EOL;

    // Example 8: Parameter-based filtering
    echo "8. Filtering by request parameters..." . PHP_EOL;
    $paramRequests = TreblleOaaS::requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->withParams('search')
        ->limit(3)
        ->get();
    
    echo "Found {$paramRequests->count()} requests containing 'search' in parameters" . PHP_EOL;

} catch (OaaSException $e) {
    echo "❌ API Error: {$e->getMessage()}" . PHP_EOL;
    echo "HTTP Status: {$e->getCode()}" . PHP_EOL;
    
    if ($e->hasResponse()) {
        $responseData = $e->getResponseData();
        if (isset($responseData['message'])) {
            echo "API Message: {$responseData['message']}" . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "❌ Unexpected Error: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL . "=== Examples completed ===" . PHP_EOL;