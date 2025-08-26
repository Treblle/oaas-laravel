<?php

namespace Treblle\OaaS\Data;

use DateTime;
use JsonSerializable;

class RequestSummary implements JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private readonly string $type,
        private readonly string $path,
        private readonly string $endpointPath,
        private readonly string $method,
        private readonly int $httpCode,
        private readonly ?string $externalUserId,
        private readonly ?string $customerDisplayName,
        private readonly string $location,
        private readonly string $ipAddress,
        private readonly ?string $appName,
        private readonly array $formattedLoadTime,
        private readonly array $formattedResponseSize,
        private readonly bool $hasAuth,
        private readonly string $threatLevel,
        private readonly DateTime $createdAt,
        private readonly array $device,
        private readonly array $api,
        private readonly ?array $endpoint,
        private readonly ?array $pathVariables,
        private readonly ?array $requestData,
        private readonly string $requestUrl,
        private readonly ?string $source,
        private readonly int $numberOfComments,
        private readonly string $logFile,
        private readonly ?array $metadata,
        private readonly ?float $latitude,
        private readonly ?float $longitude,
        private readonly ?string $externalTagId,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        $attributes = $data['attributes'];

        return new self(
            id: $data['id'],
            type: $data['type'],
            path: $attributes['path'],
            endpointPath: $attributes['endpoint_path'],
            method: $attributes['method'],
            httpCode: $attributes['http_code'],
            externalUserId: $attributes['external_user_id'],
            customerDisplayName: $attributes['customer_display_name'],
            location: $attributes['location'],
            ipAddress: $attributes['ip_address'],
            appName: $attributes['app_name'],
            formattedLoadTime: $attributes['formatted_load_time'],
            formattedResponseSize: $attributes['formatted_response_size'],
            hasAuth: $attributes['has_auth'],
            threatLevel: $attributes['threat_level'],
            createdAt: new DateTime($attributes['created_at']),
            device: $attributes['device'],
            api: $attributes['api'],
            endpoint: $attributes['endpoint'],
            pathVariables: $attributes['path_variables'],
            requestData: $attributes['request_data'],
            requestUrl: $attributes['request_url'],
            source: $attributes['source'],
            numberOfComments: $attributes['number_of_comments'],
            logFile: $attributes['log_file'],
            metadata: $attributes['metadata'],
            latitude: $attributes['latitude'],
            longitude: $attributes['longitude'],
            externalTagId: $attributes['external_tag_id'],
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

    public function getResponseSizeKb(): float
    {
        return $this->formattedResponseSize['value'] ?? 0.0;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getThreatLevel(): string
    {
        return $this->threatLevel;
    }

    public function hasAuth(): bool
    {
        return $this->hasAuth;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'path' => $this->path,
            'endpoint_path' => $this->endpointPath,
            'method' => $this->method,
            'http_code' => $this->httpCode,
            'external_user_id' => $this->externalUserId,
            'customer_display_name' => $this->customerDisplayName,
            'location' => $this->location,
            'ip_address' => $this->ipAddress,
            'app_name' => $this->appName,
            'formatted_load_time' => $this->formattedLoadTime,
            'formatted_response_size' => $this->formattedResponseSize,
            'has_auth' => $this->hasAuth,
            'threat_level' => $this->threatLevel,
            'created_at' => $this->createdAt->format('c'),
            'device' => $this->device,
            'api' => $this->api,
            'endpoint' => $this->endpoint,
            'path_variables' => $this->pathVariables,
            'request_data' => $this->requestData,
            'request_url' => $this->requestUrl,
            'source' => $this->source,
            'number_of_comments' => $this->numberOfComments,
            'log_file' => $this->logFile,
            'metadata' => $this->metadata,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'external_tag_id' => $this->externalTagId,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}