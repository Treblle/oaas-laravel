<?php

/**
 * Advanced filtering test for Treblle OaaS SDK
 * Run with: php test-scripts/test-filters.php
 */

$client = require __DIR__ . '/bootstrap.php';

use Treblle\OaaS\Enums\HttpMethod;
use Treblle\OaaS\Enums\Device;
use Treblle\OaaS\Enums\TimePeriod;

// Test configuration - update with your actual IDs
$workspaceId = 'your-workspace-id';
$apiId = 'your-api-id';
$customerId = 'test-customer-123';

echo "🧪 Testing advanced filtering capabilities..." . PHP_EOL . PHP_EOL;

$tests = [
    [
        'name' => 'POST Requests Only',
        'filters' => fn($client) => $client->requests($workspaceId, $apiId)
            ->whereCustomer($customerId)
            ->whereMethod(HttpMethod::POST)
            ->limit(3)
    ],
    [
        'name' => 'Mobile Device Requests (iOS)',
        'filters' => fn($client) => $client->requests($workspaceId, $apiId)
            ->whereCustomer($customerId)
            ->whereDevice(Device::IOS)
            ->limit(3)
    ],
    [
        'name' => 'Successful Requests (2xx)',
        'filters' => fn($client) => $client->requests($workspaceId, $apiId)
            ->whereCustomer($customerId)
            ->whereSuccessful()
            ->limit(3)
    ],
    [
        'name' => 'Client Error Requests (4xx)',
        'filters' => fn($client) => $client->requests($workspaceId, $apiId)
            ->whereCustomer($customerId)
            ->whereClientError()
            ->limit(3)
    ],
    [
        'name' => 'Requests with Problems',
        'filters' => fn($client) => $client->requests($workspaceId, $apiId)
            ->whereCustomer($customerId)
            ->withProblems()
            ->limit(3)
    ],
    [
        'name' => 'Last Week Requests',
        'filters' => fn($client) => $client->requests($workspaceId, $apiId)
            ->whereCustomer($customerId)
            ->whereTimePeriod(TimePeriod::LAST_WEEK)
            ->limit(3)
    ],
    [
        'name' => 'Slowest Requests First',
        'filters' => fn($client) => $client->requests($workspaceId, $apiId)
            ->whereCustomer($customerId)
            ->sortByLoadTimeSlowest()
            ->limit(3)
    ],
    [
        'name' => 'Complex Filter Chain',
        'filters' => fn($client) => $client->requests($workspaceId, $apiId)
            ->whereCustomer($customerId)
            ->whereMethod(HttpMethod::GET)
            ->whereSuccessful()
            ->whereTimePeriod(TimePeriod::LAST_24_HOURS)
            ->withoutProblems()
            ->sortByCreatedAtDesc()
            ->limit(2)
    ],
];

foreach ($tests as $index => $test) {
    echo ($index + 1) . ". Testing: {$test['name']}" . PHP_EOL;
    echo str_repeat("-", 50) . PHP_EOL;
    
    try {
        $filters = $test['filters']($client);
        $requests = $filters->get();
        
        echo "✅ Found {$requests->count()} requests" . PHP_EOL;
        
        // Show filter parameters
        echo "🔧 Filter parameters: " . json_encode($filters->toArray(), JSON_PRETTY_PRINT) . PHP_EOL;
        
        if ($requests->isNotEmpty()) {
            echo "📋 Sample results:" . PHP_EOL;
            
            foreach ($requests as $request) {
                echo "   • {$request->getMethod()} {$request->getPath()} [{$request->getHttpCode()}] - {$request->getLoadTimeMs()}ms" . PHP_EOL;
            }
        } else {
            echo "ℹ️ No requests match these criteria" . PHP_EOL;
        }
        
    } catch (\Treblle\OaaS\Exceptions\OaaSException $e) {
        echo "❌ API Error: {$e->getMessage()} (Status: {$e->getCode()})" . PHP_EOL;
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}" . PHP_EOL;
    }
    
    echo PHP_EOL;
}

// Test pagination
echo "🧪 Testing Pagination..." . PHP_EOL;
echo str_repeat("-", 50) . PHP_EOL;

try {
    $paginatedRequests = $client->requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->paginate(perPage: 2, page: 1);
    
    echo "✅ Pagination test successful" . PHP_EOL;
    echo "📄 Page {$paginatedRequests->currentPage()} of {$paginatedRequests->totalPages()}" . PHP_EOL;
    echo "📊 Showing {$paginatedRequests->count()} of {$paginatedRequests->total()} total requests" . PHP_EOL;
    echo "⏭️ Has next page: " . ($paginatedRequests->hasNextPage() ? 'Yes' : 'No') . PHP_EOL;
    echo "⏮️ Has previous page: " . ($paginatedRequests->hasPrevPage() ? 'Yes' : 'No') . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Pagination Error: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL;

// Test helper methods
echo "🧪 Testing Helper Methods..." . PHP_EOL;
echo str_repeat("-", 50) . PHP_EOL;

try {
    $filters = $client->requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->limit(5);
    
    // Test first() method
    $firstRequest = $filters->first();
    if ($firstRequest) {
        echo "✅ first() method: Found request {$firstRequest->getId()}" . PHP_EOL;
    } else {
        echo "ℹ️ first() method: No requests found" . PHP_EOL;
    }
    
    // Test count() method
    $totalCount = $filters->count();
    echo "✅ count() method: {$totalCount} total requests" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Helper methods error: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL . "🏁 Filter tests completed." . PHP_EOL;