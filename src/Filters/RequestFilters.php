<?php

namespace Treblle\OaaS\Filters;

use Treblle\OaaS\Contracts\OaaSClientInterface;
use Treblle\OaaS\Data\RequestCollection;
use Treblle\OaaS\Enums\Device;
use Treblle\OaaS\Enums\HttpMethod;
use Treblle\OaaS\Enums\SortOrder;
use Treblle\OaaS\Enums\TimePeriod;

class RequestFilters
{
    private array $filters = [];
    private ?int $limit = null;
    private ?int $page = null;
    private ?string $sort = null;

    public function __construct(
        private readonly OaaSClientInterface $client,
        private readonly string $workspaceId,
        private readonly string $apiId,
    ) {
    }

    public function whereCustomer(string $customerId): self
    {
        $this->filters['filter[external_user_id]'] = $customerId;
        return $this;
    }

    public function whereLocation(string $location): self
    {
        $this->filters['filter[location]'] = $location;
        return $this;
    }

    public function withParams(string $params): self
    {
        $this->filters['filter[params]'] = $params;
        return $this;
    }

    public function whereMethod(HttpMethod $method): self
    {
        $this->filters['filter[method]'] = $method->value;
        return $this;
    }

    public function whereDevice(Device $device): self
    {
        $this->filters['filter[device]'] = $device->value;
        return $this;
    }

    public function whereHttpCode(int|string $httpCode): self
    {
        $this->filters['filter[http_code]'] = $httpCode;
        return $this;
    }

    public function whereSuccessful(): self
    {
        return $this->whereHttpCode('2xx');
    }

    public function whereClientError(): self
    {
        return $this->whereHttpCode('4xx');
    }

    public function whereServerError(): self
    {
        return $this->whereHttpCode('5xx');
    }

    public function whereTimePeriod(TimePeriod $timePeriod): self
    {
        $this->filters['filter[time_period]'] = $timePeriod->value;
        return $this;
    }

    public function withProblems(): self
    {
        $this->filters['filter[has_problems]'] = 1;
        return $this;
    }

    public function withoutProblems(): self
    {
        $this->filters['filter[has_problems]'] = 0;
        return $this;
    }

    public function sortBy(SortOrder $sort): self
    {
        $this->sort = $sort->value;
        return $this;
    }

    public function sortByCreatedAtDesc(): self
    {
        return $this->sortBy(SortOrder::CREATED_AT_DESC);
    }

    public function sortByCreatedAtAsc(): self
    {
        return $this->sortBy(SortOrder::CREATED_AT_ASC);
    }

    public function sortByLoadTimeFastest(): self
    {
        return $this->sortBy(SortOrder::LOAD_TIME_ASC);
    }

    public function sortByLoadTimeSlowest(): self
    {
        return $this->sortBy(SortOrder::LOAD_TIME_DESC);
    }

    public function limit(int $limit): self
    {
        $this->limit = max(1, min($limit, config('treblle-oaas.max_limit', 50)));
        return $this;
    }

    public function page(int $page): self
    {
        $this->page = max(1, $page);
        return $this;
    }

    public function toArray(): array
    {
        $params = $this->filters;

        if ($this->limit !== null) {
            $params['limit'] = $this->limit;
        }

        if ($this->page !== null) {
            $params['page'] = $this->page;
        }

        if ($this->sort !== null) {
            $params['sort'] = $this->sort;
        }

        return $params;
    }

    public function get(): RequestCollection
    {
        return $this->client->getRequests($this->workspaceId, $this->apiId, $this);
    }

    public function first(): ?array
    {
        $collection = $this->limit(1)->get();
        return $collection->isEmpty() ? null : $collection->first();
    }

    public function count(): int
    {
        return $this->get()->total();
    }

    public function paginate(int $perPage = null, int $page = 1): RequestCollection
    {
        $perPage = $perPage ?? config('treblle-oaas.default_limit', 20);
        
        return $this->limit($perPage)->page($page)->get();
    }
}