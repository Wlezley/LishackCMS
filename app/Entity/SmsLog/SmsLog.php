<?php

declare(strict_types=1);

namespace App\Entity\SmsLog;

use App\Entity\BaseEntity;
use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\HasIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'log_sms')] // TODO: change table name to sms_log ???
class SmsLog extends BaseEntity
{
    use HasIdTrait;
    use CreatedAtTrait;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 16)]
        private string $phoneNumber, // TODO: change to phone ???
        #[ORM\Column(type: Types::STRING, length: 460)]
        private string $message,
        #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => null])]
        private ?int $userId = null,
        #[ORM\Column(type: Types::SMALLINT, length: 3, nullable: true, options: ['default' => null])]
        private ?int $errorCode = null,
    ) {
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @throws InvalidArgumentException 460 characters max
     */
    public function setMessage(string $message): self
    {
        Assert::maxLength($message, 460, 'SMS Message length must be less than 460 characters');
        $this->message = $message;
        return $this;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function setErrorCode(?int $errorCode): self
    {
        $this->errorCode = $errorCode;
        return $this;
    }
}
