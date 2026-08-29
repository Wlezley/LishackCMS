<?php

declare(strict_types=1);

namespace App\Entity\Dataset;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Dataset>
 */
final readonly class DatasetRepository extends BaseRepository implements DatasetRepositoryInterface
{
}
