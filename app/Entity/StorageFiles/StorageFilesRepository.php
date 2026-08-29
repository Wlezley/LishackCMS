<?php

declare(strict_types=1);

namespace App\Entity\StorageFiles;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<StorageFiles>
 */
final readonly class StorageFilesRepository extends BaseRepository implements StorageFilesRepositoryInterface
{
}
