<?php

/**
 * Request details test for Treblle OaaS SDK
 * Run with: php test-scripts/test-request-details.php
 */

$client = require __DIR__ . '/bootstrap.php';

// Test configuration - update with your actual IDs
$workspaceId = 'your-workspace-id';
$apiId = 'your-api-id';
$customerId = 'test-customer-123';

echo "🧪 Testing request details functionality..." . PHP_EOL . PHP_EOL;

try {
    // First, get a list of requests to work with
    echo "1. Getting list of requests..." . PHP_EOL;
    
    $requests = $client->requests($workspaceId, $apiId)
        ->whereCustomer($customerId)
        ->limit(3)
        ->get();

    if ($requests->isEmpty()) {
        echo "ℹ️ No requests found for customer '{$customerId}'" . PHP_EOL;
        echo "💡 Try using a different customer ID or check your data." . PHP_EOL;
        exit;
    }

    echo "✅ Found {$requests->count()} requests to analyze" . PHP_EOL . PHP_EOL;

    // Test request details for each found request
    foreach ($requests as $index => $request) {
        $requestNumber = $index + 1;
        echo "{$requestNumber}. Analyzing Request: {$request->getId()}" . PHP_EOL;
        echo str_repeat("-", 60) . PHP_EOL;
        
        try {
            $details = $client->getRequest($workspaceId, $apiId, $request->getId());
            
            // Basic information
            echo "📝 Basic Information:" . PHP_EOL;
            echo "   Request ID: {$details->getId()}" . PHP_EOL;
            echo "   Method: {$details->getMethod()}" . PHP_EOL;
            echo "   Path: {$details->getPath()}" . PHP_EOL;
            echo "   HTTP Status: {$details->getHttpCode()}";
            
            if ($details->isSuccessful()) {
                echo " ✅ Success" . PHP_EOL;
            } elseif ($details->hasClientError()) {
                echo " ⚠️ Client Error" . PHP_EOL;
            } elseif ($details->hasServerError()) {
                echo " ❌ Server Error" . PHP_EOL;
            } else {
                echo PHP_EOL;
            }
            
            echo "   Customer: " . ($details->getCustomerDisplayName() ?: $details->getExternalUserId()) . PHP_EOL;
            echo "   Load Time: {$details->getLoadTimeMs()}ms";
            if ($details->isLoadTimeFast()) {
                echo " ⚡ Fast" . PHP_EOL;
            } else {
                echo " 🐌 Slow" . PHP_EOL;
            }
            echo "   Created: " . $details->getCreatedAt()->format('Y-m-d H:i:s T') . PHP_EOL;
            echo PHP_EOL;
            
            // Request headers (sample)
            echo "📨 Request Headers (sample):" . PHP_EOL;
            $requestHeaders = $details->getRequestHeaders();
            if (!empty($requestHeaders)) {
                $sampleHeaders = array_slice($requestHeaders, 0, 5, true);
                foreach ($sampleHeaders as $name => $value) {
                    $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                    echo "   {$name}: {$displayValue}" . PHP_EOL;
                }
                if (count($requestHeaders) > 5) {
                    echo "   ... and " . (count($requestHeaders) - 5) . " more headers" . PHP_EOL;
                }
            } else {
                echo "   No headers available" . PHP_EOL;
            }
            echo PHP_EOL;
            
            // Response headers (sample)
            echo "📬 Response Headers (sample):" . PHP_EOL;
            $responseHeaders = $details->getResponseHeaders();
            if (!empty($responseHeaders)) {
                $sampleHeaders = array_slice($responseHeaders, 0, 5, true);
                foreach ($sampleHeaders as $name => $value) {
                    $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                    echo "   {$name}: {$displayValue}" . PHP_EOL;
                }
                if (count($responseHeaders) > 5) {
                    echo "   ... and " . (count($responseHeaders) - 5) . " more headers" . PHP_EOL;
                }
            } else {
                echo "   No headers available" . PHP_EOL;
            }
            echo PHP_EOL;
            
            // Response body (truncated)
            echo "📦 Response Body (first 200 chars):" . PHP_EOL;
            $responseBody = $details->getResponseBody();
            if (!empty($responseBody)) {
                $bodyString = json_encode($responseBody);
                $truncatedBody = strlen($bodyString) > 200 ? substr($bodyString, 0, 200) . '...' : $bodyString;
                echo "   {$truncatedBody}" . PHP_EOL;
            } else {
                echo "   No response body available" . PHP_EOL;
            }
            echo PHP_EOL;
            
            // Server information
            echo "🖥️ Server Information:" . PHP_EOL;
            $serverInfo = $details->getServerInfo();
            echo "   Software: " . ($serverInfo['software'] ?? 'Unknown') . PHP_EOL;
            echo "   OS: " . ($serverInfo['name'] ?? 'Unknown') . PHP_EOL;
            echo "   Location: " . ($serverInfo['city'] ?? 'Unknown') . ", " . ($serverInfo['country'] ?? 'Unknown') . PHP_EOL;
            echo "   IP: " . ($serverInfo['ip'] ?? 'Unknown') . PHP_EOL;
            echo "   Protocol: " . ($serverInfo['protocol'] ?? 'Unknown') . PHP_EOL;
            echo "   Timezone: " . ($serverInfo['timezone'] ?? 'Unknown') . PHP_EOL;
            echo PHP_EOL;
            
            // Compliance report
            echo "📊 Compliance Report:" . PHP_EOL;
            $compliance = $details->getComplianceReport();
            echo "   Status: " . ($compliance['status'] ?? 'Unknown');
            if (isset($compliance['overall_percentage'])) {
                echo " ({$compliance['overall_percentage']}%)" . PHP_EOL;
            } else {
                echo PHP_EOL;
            }
            echo "   Report: " . ($compliance['name'] ?? 'Unknown') . PHP_EOL;
            
            if (isset($compliance['categories']) && is_array($compliance['categories'])) {
                echo "   Categories: " . count($compliance['categories']) . " checked" . PHP_EOL;
                foreach (array_slice($compliance['categories'], 0, 3) as $category) {
                    $categoryStatus = $category['status'] ?? 'Unknown';
                    $categoryName = $category['label'] ?? $category['name'] ?? 'Unknown';
                    echo "     • {$categoryName}: {$categoryStatus}" . PHP_EOL;
                }
            }
            echo PHP_EOL;
            
            // Security audit
            echo "🔒 Security Audit:" . PHP_EOL;
            $securityData = $details->getSecurityAudit();
            if (!empty($securityData)) {
                echo "   Total security checks: " . count($securityData) . PHP_EOL;
                foreach (array_slice($securityData, 0, 3) as $audit) {
                    $auditName = $audit['audit'] ?? 'Unknown audit';
                    $auditStatus = $audit['status'] ?? 'Unknown';
                    $auditImpact = $audit['impact'] ?? 'Unknown';
                    echo "     • {$auditName}: {$auditStatus} (Impact: {$auditImpact})" . PHP_EOL;
                }
                if (count($securityData) > 3) {
                    echo "     ... and " . (count($securityData) - 3) . " more security checks" . PHP_EOL;
                }
            } else {
                echo "   No security audit data available" . PHP_EOL;
            }
            echo PHP_EOL;
            
            // Problems check
            if ($details->hasProblem()) {
                echo "⚠️ Request Problems:" . PHP_EOL;
                $problem = $details->getProblem();
                echo "   This request has identified problems." . PHP_EOL;
                if (is_array($problem)) {
                    echo "   Problem details: " . json_encode($problem, JSON_PRETTY_PRINT) . PHP_EOL;
                }
            } else {
                echo "✅ No problems detected with this request." . PHP_EOL;
            }
            
        } catch (\Treblle\OaaS\Exceptions\OaaSException $e) {
            echo "❌ Failed to get details for request {$request->getId()}: {$e->getMessage()}" . PHP_EOL;
        }
        
        echo PHP_EOL . str_repeat("=", 80) . PHP_EOL . PHP_EOL;
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
    
} catch (Exception $e) {
    echo "❌ Unexpected Error: {$e->getMessage()}" . PHP_EOL;
    echo "📍 File: {$e->getFile()}:{$e->getLine()}" . PHP_EOL;
}

echo "🏁 Request details test completed." . PHP_EOL;