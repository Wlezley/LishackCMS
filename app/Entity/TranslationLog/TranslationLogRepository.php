<?php

declare(strict_types=1);

namespace App\Entity\TranslationLog;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<TranslationLog>
 */
final readonly class TranslationLogRepository extends BaseRepository implements TranslationLogRepositoryInterface
{
}
