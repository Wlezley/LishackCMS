<?php

declare(strict_types=1);

namespace App\Entity\DatasetColumn;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<DatasetColumn>
 */
interface DatasetColumnRepositoryInterface extends BaseRepositoryInterface
{
    /** @return DatasetColumn[] */
    public function findByDatasetId(int $datasetId, bool $deletedOnly = false): array;
}
