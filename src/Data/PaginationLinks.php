<?php

namespace Treblle\OaaS\Data;

class PaginationLinks
{
    public function __construct(
        private readonly ?string $first,
        private readonly ?string $last,
        private readonly ?string $next,
        private readonly ?string $prev,
    ) {
    }

    public static function fromApiResponse(array $links): self
    {
        return new self(
            first: $links['first'] ?? null,
            last: $links['last'] ?? null,
            next: $links['next'] ?? null,
            prev: $links['prev'] ?? null,
        );
    }

    public function getFirst(): ?string
    {
        return $this->first;
    }

    public function getLast(): ?string
    {
        return $this->last;
    }

    public function getNext(): ?string
    {
        return $this->next;
    }

    public function getPrev(): ?string
    {
        return $this->prev;
    }

    public function hasNext(): bool
    {
        return $this->next !== null;
    }

    public function hasPrev(): bool
    {
        return $this->prev !== null;
    }

    public function toArray(): array
    {
        return [
            'first' => $this->first,
            'last' => $this->last,
            'next' => $this->next,
            'prev' => $this->prev,
        ];
    }
}