<?php

namespace Treblle\OaaS\Data;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class RequestCollection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    private array $requests;
    private PaginationMeta $meta;
    private PaginationLinks $links;
    private string $message;

    public function __construct(array $requests, PaginationMeta $meta, PaginationLinks $links, string $message)
    {
        $this->requests = $requests;
        $this->meta = $meta;
        $this->links = $links;
        $this->message = $message;
    }

    public static function fromApiResponse(array $response): self
    {
        $requests = array_map(
            fn(array $item) => RequestSummary::fromApiResponse($item),
            $response['data'] ?? []
        );

        return new self(
            requests: $requests,
            meta: PaginationMeta::fromApiResponse($response['meta'] ?? []),
            links: PaginationLinks::fromApiResponse($response['links'] ?? []),
            message: $response['message'] ?? ''
        );
    }

    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getMeta(): PaginationMeta
    {
        return $this->meta;
    }

    public function getLinks(): PaginationLinks
    {
        return $this->links;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function count(): int
    {
        return count($this->requests);
    }

    public function total(): int
    {
        return $this->meta->getTotal();
    }

    public function currentPage(): int
    {
        return $this->meta->getCurrentPage();
    }

    public function totalPages(): int
    {
        return $this->meta->getTotalPages();
    }

    public function perPage(): int
    {
        return $this->meta->getPerPage();
    }

    public function hasNextPage(): bool
    {
        return $this->links->hasNext();
    }

    public function hasPrevPage(): bool
    {
        return $this->links->hasPrev();
    }

    public function isEmpty(): bool
    {
        return empty($this->requests);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function first(): ?RequestSummary
    {
        return $this->requests[0] ?? null;
    }

    public function last(): ?RequestSummary
    {
        return end($this->requests) ?: null;
    }

    public function toArray(): array
    {
        return array_map(fn($request) => $request->toArray(), $this->requests);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'data' => $this->toArray(),
            'meta' => $this->meta->toArray(),
            'links' => $this->links->toArray(),
            'message' => $this->message,
        ];
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->requests);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->requests[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->requests[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->requests[] = $value;
        } else {
            $this->requests[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->requests[$offset]);
    }
}