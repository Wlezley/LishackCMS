<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\SortOrderEnum;

final readonly class PaginationDTO
{
    public function __construct(
        public ?int $page = null,
        public ?int $perPage = null,
        public ?string $sortBy = null,
        public SortOrderEnum $sortOrder = SortOrderEnum::Asc,
    ) {
    }

    public function isPaginated(): bool
    {
        return $this->page !== null && $this->perPage !== null;
    }

    public function getOffset(): ?int
    {
        if (!$this->isPaginated()) {
            return null;
        }

        return ($this->page - 1) * $this->perPage;
    }

    public function getLimit(): ?int
    {
        if (!$this->isPaginated()) {
            return null;
        }

        return $this->perPage;
    }

    public function nextPage(): self
    {
        return new self(
            page: ($this->page ?? 1) + 1,
            perPage: $this->perPage,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder,
        );
    }

    public function previousPage(): self
    {
        return new self(
            page: max(1, ($this->page ?? 1) - 1),
            perPage: $this->perPage,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder,
        );
    }

    public function withPage(int $page): self
    {
        return new self(
            page: $page,
            perPage: $this->perPage,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder,
        );
    }

    public function withPerPage(int $perPage): self
    {
        return new self(
            page: 1,
            perPage: $perPage,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder,
        );
    }

    public function withSort(
        ?string $sortBy,
        SortOrderEnum $sortOrder = SortOrderEnum::Asc,
    ): self {
        return new self(
            page: $this->page,
            perPage: $this->perPage,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        );
    }

    public function isFirstPage(): bool
    {
        return $this->page === 1;
    }

    public function totalPages(int $total): ?int
    {
        if (!$this->isPaginated()) {
            return null;
        }

        return (int) ceil($total / $this->perPage);
    }

    public function hasPreviousPage(): bool
    {
        return $this->page !== null && $this->page > 1;
    }

    public function hasNextPage(int $total): bool
    {
        $totalPages = $this->totalPages($total);

        return $totalPages !== null
            && $this->page !== null
            && $this->page < $totalPages;
    }

    public function isLastPage(int $total): bool
    {
        if (!$this->isPaginated()) {
            return false;
        }

        return !$this->hasNextPage($total);
    }

    public function firstItem(int $total): ?int
    {
        $offset = $this->getOffset();

        if ($offset === null || $total === 0 || $offset >= $total) {
            return null;
        }

        return $offset + 1;
    }

    public function lastItem(int $total): ?int
    {
        $offset = $this->getOffset();

        if ($offset === null || $total === 0 || $offset >= $total) {
            return null;
        }

        return min(
            $offset + $this->perPage,
            $total,
        );
    }
}
