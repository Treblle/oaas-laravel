<?php

namespace Treblle\OaaS\Data;

use DateTime;
use JsonSerializable;

class RequestDetails implements JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private readonly string $type,
        private readonly string $path,
        private readonly ?string $endpointPath,
        private readonly array $pathVariables,
        private readonly int $httpCode,
        private readonly string $method,
        private readonly array $device,
        private readonly string $source,
        private readonly string $threatLevel,
        private readonly bool $hasAuth,
        private readonly ?string $location,
        private readonly ?float $latitude,
        private readonly ?float $longitude,
        private readonly ?string $ipAddress,
        private readonly string $appName,
        private readonly array $formattedLoadTime,
        private readonly array $formattedResponseSize,
        private readonly string $requestUrl,
        private readonly array $requestData,
        private readonly int $numberOfComments,
        private readonly string $logFile,
        private readonly DateTime $createdAt,
        private readonly ?string $externalUserId,
        private readonly ?string $customerDisplayName,
        private readonly ?string $externalTagId,
        private readonly array $endpoint,
        private readonly ?array $problem,
        private readonly array $metadata,
        private readonly array $request,
        private readonly array $response,
        private readonly array $server,
        private readonly array $compliance,
        private readonly array $securityData,
    ) {
    }

    public static function fromApiResponse(array $response): self
    {
        $data = $response['data'];
        $attributes = $data['attributes'];

        return new self(
            id: $data['id'],
            type: $data['type'],
            path: $attributes['path'],
            endpointPath: $attributes['endpoint_path'],
            pathVariables: $attributes['path_variables'],
            httpCode: $attributes['http_code'],
            method: $attributes['method'],
            device: $attributes['device'],
            source: $attributes['source'],
            threatLevel: $attributes['threat_level'],
            hasAuth: $attributes['has_auth'],
            location: $attributes['location'],
            latitude: $attributes['latitude'],
            longitude: $attributes['longitude'],
            ipAddress: $attributes['ip_address'],
            appName: $attributes['app_name'],
            formattedLoadTime: $attributes['formatted_load_time'],
            formattedResponseSize: $attributes['formatted_response_size'],
            requestUrl: $attributes['request_url'],
            requestData: $attributes['request_data'],
            numberOfComments: $attributes['number_of_comments'],
            logFile: $attributes['log_file'],
            createdAt: new DateTime($attributes['created_at']),
            externalUserId: $attributes['external_user_id'],
            customerDisplayName: $attributes['customer_display_name'],
            externalTagId: $attributes['external_tag_id'],
            endpoint: $attributes['endpoint'] ?? [],
            problem: $attributes['problem'],
            metadata: $attributes['metadata'],
            request: $attributes['request'],
            response: $attributes['response'],
            server: $attributes['server'],
            compliance: $attributes['compliance'] ?? [],
            securityData: $attributes['security_data'] ?? [],
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getExternalUserId(): ?string
    {
        return $this->externalUserId;
    }

    public function getCustomerDisplayName(): ?string
    {
        return $this->customerDisplayName;
    }

    public function getRequestHeaders(): array
    {
        return $this->request['headers'] ?? [];
    }

    public function getResponseHeaders(): array
    {
        return $this->response['headers'] ?? [];
    }

    public function getResponseBody(): array
    {
        return $this->response['body'] ?? [];
    }

    public function getServerInfo(): array
    {
        return $this->server;
    }

    public function getComplianceReport(): array
    {
        return $this->compliance;
    }

    public function getSecurityAudit(): array
    {
        return $this->securityData;
    }

    public function hasProblem(): bool
    {
        return $this->problem !== null;
    }

    public function getProblem(): ?array
    {
        return $this->problem;
    }

    public function isSuccessful(): bool
    {
        return $this->httpCode >= 200 && $this->httpCode < 300;
    }

    public function hasClientError(): bool
    {
        return $this->httpCode >= 400 && $this->httpCode < 500;
    }

    public function hasServerError(): bool
    {
        return $this->httpCode >= 500;
    }

    public function getLoadTimeMs(): float
    {
        return $this->formattedLoadTime['value'] ?? 0.0;
    }

    public function isLoadTimeFast(): bool
    {
        return $this->formattedLoadTime['is_good'] ?? false;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'path' => $this->path,
            'endpoint_path' => $this->endpointPath,
            'path_variables' => $this->pathVariables,
            'http_code' => $this->httpCode,
            'method' => $this->method,
            'device' => $this->device,
            'source' => $this->source,
            'threat_level' => $this->threatLevel,
            'has_auth' => $this->hasAuth,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ip_address' => $this->ipAddress,
            'app_name' => $this->appName,
            'formatted_load_time' => $this->formattedLoadTime,
            'formatted_response_size' => $this->formattedResponseSize,
            'request_url' => $this->requestUrl,
            'request_data' => $this->requestData,
            'number_of_comments' => $this->numberOfComments,
            'log_file' => $this->logFile,
            'created_at' => $this->createdAt->format('c'),
            'external_user_id' => $this->externalUserId,
            'customer_display_name' => $this->customerDisplayName,
            'external_tag_id' => $this->externalTagId,
            'endpoint' => $this->endpoint,
            'problem' => $this->problem,
            'metadata' => $this->metadata,
            'request' => $this->request,
            'response' => $this->response,
            'server' => $this->server,
            'compliance' => $this->compliance,
            'security_data' => $this->securityData,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}