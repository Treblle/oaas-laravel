<?php

/**
 * Basic functionality test for Treblle OaaS SDK
 * Run with: php test-scripts/test-basic.php
 */

$client = require __DIR__ . '/bootstrap.php';

// Test configuration - update with your actual IDs
$workspaceId = 'your-workspace-id';
$apiId = 'your-api-id';
$customerId = 'test-customer-123';

try {
    echo "🧪 Testing basic request filtering..." . PHP_EOL;
    
    $requests = $client->requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->whereTimePeriod(\Treblle\OaaS\Enums\TimePeriod::LAST_24_HOURS)
        ->limit(5)
        ->get();

    echo "✅ Found {$requests->count()} requests out of {$requests->total()} total." . PHP_EOL;
    echo "📄 Current page: {$requests->currentPage()} / {$requests->totalPages()}" . PHP_EOL . PHP_EOL;
    
    if ($requests->isNotEmpty()) {
        echo "📋 Request Details:" . PHP_EOL;
        echo str_repeat("-", 60) . PHP_EOL;
        
        foreach ($requests as $index => $request) {
            echo ($index + 1) . ". {$request->getMethod()} {$request->getPath()}" . PHP_EOL;
            echo "   Status: {$request->getHttpCode()}";
            
            if ($request->isSuccessful()) {
                echo " ✅ Success";
            } elseif ($request->hasClientError()) {
                echo " ⚠️ Client Error";
            } elseif ($request->hasServerError()) {
                echo " ❌ Server Error";
            }
            echo PHP_EOL;
            
            echo "   Load Time: {$request->getLoadTimeMs()}ms";
            if ($request->isLoadTimeFast()) {
                echo " ⚡ Fast";
            } else {
                echo " 🐌 Slow";
            }
            echo PHP_EOL;
            
            echo "   Customer: " . ($request->getCustomerDisplayName() ?: $request->getExternalUserId()) . PHP_EOL;
            echo "   Location: {$request->getLocation()}" . PHP_EOL;
            echo "   Threat Level: {$request->getThreatLevel()}" . PHP_EOL;
            echo "   Has Auth: " . ($request->hasAuth() ? 'Yes' : 'No') . PHP_EOL;
            echo "   Created: " . $request->getCreatedAt()->format('Y-m-d H:i:s') . PHP_EOL;
            echo PHP_EOL;
        }
    } else {
        echo "ℹ️ No requests found for customer '{$customerId}' in the last 24 hours." . PHP_EOL;
        echo "💡 Try adjusting the time period or customer ID." . PHP_EOL;
    }
    
} catch (\Treblle\OaaS\Exceptions\OaaSException $e) {
    echo "❌ API Error: {$e->getMessage()}" . PHP_EOL;
    echo "📊 HTTP Status: {$e->getCode()}" . PHP_EOL;
    
    if ($e->hasResponse()) {
        $responseData = $e->getResponseData();
        if (isset($responseData['message'])) {
            echo "📝 API Message: {$responseData['message']}" . PHP_EOL;
        }
    }
    
    echo PHP_EOL;
    echo "🔍 Troubleshooting:" . PHP_EOL;
    echo "   • Check your API token in bootstrap.php" . PHP_EOL;
    echo "   • Verify workspace and API IDs" . PHP_EOL;
    echo "   • Ensure the customer ID exists in your data" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Unexpected Error: {$e->getMessage()}" . PHP_EOL;
    echo "📍 File: {$e->getFile()}:{$e->getLine()}" . PHP_EOL;
}

echo PHP_EOL . "🏁 Test completed." . PHP_EOL;