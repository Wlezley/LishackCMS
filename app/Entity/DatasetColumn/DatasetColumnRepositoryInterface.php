<?php

declare(strict_types=1);

namespace App\Entity\DatasetColumn;

use App\Entity\BaseRepositoryInterface;
use App\Enum\Dataset\DatasetColumnType;

/**
 * @extends BaseRepositoryInterface<DatasetColumn>
 */
interface DatasetColumnRepositoryInterface extends BaseRepositoryInterface
{
    /** @return DatasetColumn[] */
    public function findByDatasetId(int $datasetId, bool $includeDeleted = false): array;

    /** @return DatasetColumn[] */
    public function findByDatasetIdFiltered(
        int $datasetId,
        ?DatasetColumnType $type = null,
        ?bool $filterRequired = null,
        ?bool $filterListed = null,
        ?bool $filterHidden = null,
        ?bool $filterDeleted = null,
    ): array;
}
