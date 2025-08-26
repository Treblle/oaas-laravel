<?php

/**
 * Bootstrap file for standalone testing of Treblle OaaS SDK
 * This allows testing without Laravel framework
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Treblle\OaaS\OaaSClient;

// Configuration - update these with your test values
$config = [
    'api_token' => getenv('TREBLLE_OAAS_API_TOKEN') ?: 'your-test-token-here',
    'base_url' => getenv('TREBLLE_OAAS_BASE_URL') ?: 'https://api-forge.treblle.com/api/v1',
    'timeout' => 30,
    'connect_timeout' => 10,
];

// Create client directly (without Laravel container)
$client = new OaaSClient(
    apiToken: $config['api_token'],
    baseUrl: $config['base_url'],
    timeout: $config['timeout'],
    connectTimeout: $config['connect_timeout']
);

echo "=== Treblle OaaS SDK Test Environment ===" . PHP_EOL;
echo "Base URL: {$config['base_url']}" . PHP_EOL;
echo "API Token: " . (strlen($config['api_token']) > 10 ? substr($config['api_token'], 0, 10) . '...' : 'NOT SET') . PHP_EOL;
echo "=========================================" . PHP_EOL . PHP_EOL;

return $client;