<?php

declare(strict_types=1);

namespace App\Entity\SmsLog;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<SmsLog>
 */
final readonly class SmsLogRepository extends BaseRepository implements SmsLogRepositoryInterface
{
    public function create(
        string $phoneNumber,
        string $message,
        ?int $userId = null,
        ?int $errorCode = null,
    ): SmsLog {
        $log = new SmsLog(
            phoneNumber: $phoneNumber,
            message: $message,
            userId: $userId,
            errorCode: $errorCode,
        );

        $this->save($log);

        return $log;
    }
}
