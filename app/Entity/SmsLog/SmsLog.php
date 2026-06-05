<?php

declare(strict_types=1);

namespace App\Entity\SmsLog;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'log_sms')] // TODO: change table name to sms_log
class SmsLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $userId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: Types::TEXT, length: 16)]
    private string $phoneNumber; // TODO: change to phone ???

    #[ORM\Column(type: Types::TEXT, length: 460)]
    private string $message;

    #[ORM\Column(type: Types::SMALLINT, length: 3, nullable: true)]
    private ?int $errorCode = null;

    public function __construct(
        int $userId,
        \DateTimeImmutable $date,
        string $phoneNumber,
        string $message,
        int $errorCode,
    ) {
        $this->userId = $userId;
        $this->date = $date;
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->errorCode = $errorCode;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @throws InvalidArgumentException 460 characters max
     */
    public function setMessage(string $message): void
    {
        Assert::maxLength($message, 460, 'SMS Message length must be less than 460 characters');
        $this->message = $message;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function setErrorCode(?int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }
}
