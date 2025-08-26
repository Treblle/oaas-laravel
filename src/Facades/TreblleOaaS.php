<?php

namespace Treblle\OaaS\Facades;

use Illuminate\Support\Facades\Facade;
use Treblle\OaaS\Contracts\OaaSClientInterface;
use Treblle\OaaS\Data\RequestCollection;
use Treblle\OaaS\Data\RequestDetails;
use Treblle\OaaS\Filters\RequestFilters;

/**
 * @method static RequestFilters requests(string $workspaceId, string $apiId)
 * @method static RequestCollection getRequests(string $workspaceId, string $apiId, RequestFilters $filters)
 * @method static RequestDetails getRequest(string $workspaceId, string $apiId, string $requestId)
 *
 * @see \Treblle\OaaS\OaaSClient
 */
class TreblleOaaS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OaaSClientInterface::class;
    }
}