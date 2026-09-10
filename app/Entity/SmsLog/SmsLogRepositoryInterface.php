<?php

declare(strict_types=1);

namespace App\Entity\SmsLog;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<SmsLog>
 */
interface SmsLogRepositoryInterface extends BaseRepositoryInterface
{
    public function create(
        string $phoneNumber,
        string $message,
        ?int $userId = null,
        ?int $errorCode = null,
    ): SmsLog;
}
