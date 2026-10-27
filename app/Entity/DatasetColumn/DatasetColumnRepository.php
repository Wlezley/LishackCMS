<?php

declare(strict_types=1);

namespace App\Entity\DatasetColumn;

use App\Entity\BaseRepository;
use App\Enum\Dataset\DatasetColumnType;

/**
 * @extends BaseRepository<DatasetColumn>
 */
final readonly class DatasetColumnRepository extends BaseRepository implements DatasetColumnRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByDatasetId(int $datasetId, bool $includeDeleted = false): array
    {
        if ($includeDeleted) {
            return $this->findBy(['dataset' => $datasetId]);
        }

        return $this->findBy(['dataset' => $datasetId, 'deleted' => false]);
    }

    /**
     * @inheritDoc
     */
    public function findByDatasetIdFiltered(
        int $datasetId,
        ?DatasetColumnType $type = null,
        ?bool $filterRequired = null,
        ?bool $filterListed = null,
        ?bool $filterHidden = null,
        ?bool $filterDeleted = null,
    ): array {
        /** @var array<string, mixed> $criteria */
        $criteria = ['dataset' => $datasetId];

        if ($type !== null) {
            $criteria['type'] = $type;
        }
        if ($filterRequired !== null) {
            $criteria['required'] = $filterRequired;
        }
        if ($filterListed !== null) {
            $criteria['listed'] = $filterListed;
        }
        if ($filterHidden !== null) {
            $criteria['hidden'] = $filterHidden;
        }
        if ($filterDeleted !== null) {
            $criteria['deleted'] = $filterDeleted;
        }

        return $this->findBy($criteria);
    }
}
