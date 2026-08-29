<?php

declare(strict_types=1);

namespace App\Entity\SmsLog;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<SmsLog>
 */
final readonly class SmsLogRepository extends BaseRepository implements SmsLogRepositoryInterface
{
}
