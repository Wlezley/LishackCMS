<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\SortOrderEnum;

final readonly class FilterDTO
{
    /**
     * @param array<string, mixed> $criteria
     */
    public function __construct(
        public array $criteria = [],
        public ?string $sortBy = null,
        public SortOrderEnum $sortOrder = SortOrderEnum::Asc,
    ) {
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function withCriteria(array $criteria): self
    {
        return new self(
            criteria: $criteria,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder,
        );
    }

    public function withSort(
        ?string $sortBy,
        SortOrderEnum $sortOrder = SortOrderEnum::Asc,
    ): self {
        return new self(
            criteria: $this->criteria,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        );
    }
}
