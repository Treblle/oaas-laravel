<?php

namespace Treblle\OaaS\Contracts;

use Treblle\OaaS\Data\RequestCollection;
use Treblle\OaaS\Data\RequestDetails;
use Treblle\OaaS\Filters\RequestFilters;

interface OaaSClientInterface
{
    public function requests(string $workspaceId, string $apiId): RequestFilters;
    
    public function getRequests(string $workspaceId, string $apiId, RequestFilters $filters): RequestCollection;
    
    public function getRequest(string $workspaceId, string $apiId, string $requestId): RequestDetails;
}