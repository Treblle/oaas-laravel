<?php

namespace Treblle\OaaS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Treblle\OaaS\Contracts\OaaSClientInterface;
use Treblle\OaaS\Data\RequestCollection;
use Treblle\OaaS\Data\RequestDetails;
use Treblle\OaaS\Exceptions\OaaSException;
use Treblle\OaaS\Filters\RequestFilters;

class OaaSClient implements OaaSClientInterface
{
    private PendingRequest $httpClient;

    public function __construct(
        private readonly string $apiToken,
        private readonly string $baseUrl,
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 10,
    ) {
        $this->initializeHttpClient();
    }

    private function initializeHttpClient(): void
    {
        $this->httpClient = (new HttpFactory())
            ->baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
                'Content-Type' => 'application/json',
                'User-Agent' => 'TreblleOaaS/Laravel/1.0.0',
            ])
            ->withToken($this->apiToken);
    }

    public function requests(string $workspaceId, string $apiId): RequestFilters
    {
        return new RequestFilters($this, $workspaceId, $apiId);
    }

    public function getRequests(string $workspaceId, string $apiId, RequestFilters $filters): RequestCollection
    {
        $response = $this->makeRequest('GET', "/workspaces/{$workspaceId}/apis/{$apiId}/requests", [
            'query' => $filters->toArray(),
        ]);

        return RequestCollection::fromApiResponse($response->json());
    }

    public function getRequest(string $workspaceId, string $apiId, string $requestId): RequestDetails
    {
        $response = $this->makeRequest('GET', "/workspaces/{$workspaceId}/apis/{$apiId}/requests/{$requestId}");

        return RequestDetails::fromApiResponse($response->json());
    }

    private function makeRequest(string $method, string $endpoint, array $options = []): Response
    {
        try {
            $response = $this->httpClient->send($method, $endpoint, $options);

            if ($response->failed()) {
                throw new OaaSException(
                    message: $response->json('message', 'API request failed'),
                    code: $response->status(),
                    response: $response
                );
            }

            return $response;
        } catch (GuzzleException $e) {
            throw new OaaSException(
                message: "HTTP request failed: {$e->getMessage()}",
                code: $e->getCode(),
                previous: $e
            );
        }
    }
}