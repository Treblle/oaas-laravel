<?php

namespace Treblle\OaaS\Data;

class PaginationMeta
{
    public function __construct(
        private readonly int $count,
        private readonly int $currentPage,
        private readonly int $perPage,
        private readonly int $total,
        private readonly int $totalPages,
    ) {
    }

    public static function fromApiResponse(array $meta): self
    {
        $pagination = $meta['pagination'] ?? [];

        return new self(
            count: $pagination['count'] ?? 0,
            currentPage: $pagination['current_page'] ?? 1,
            perPage: $pagination['per_page'] ?? 20,
            total: $pagination['total'] ?? 0,
            totalPages: $pagination['total_pages'] ?? 0,
        );
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function toArray(): array
    {
        return [
            'pagination' => [
                'count' => $this->count,
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'total_pages' => $this->totalPages,
            ],
        ];
    }
}