<?php

declare(strict_types=1);

namespace App\Enum\Dataset;

enum DatasetColumnType: string
{
    case Int = 'int';
    case Bool = 'bool';
    case String = 'string';
    case Text = 'text';
    case Html = 'html';
    case Json = 'json';
    case Wysiwyg = 'wysiwyg';

    public function getSqlType(): string
    {
        return match ($this) {
            self::Int => 'INT(11)',
            self::Bool => 'TINYINT(1)',
            self::String => 'VARCHAR(255)',
            self::Text,
            self::Html,
            self::Json,
            self::Wysiwyg => 'LONGTEXT',
        };
    }

    public function getDoctrineType(): string
    {
        return match ($this) {
            self::Int => \Doctrine\DBAL\Types\Types::INTEGER,
            self::Bool => \Doctrine\DBAL\Types\Types::BOOLEAN,
            self::String => \Doctrine\DBAL\Types\Types::STRING,
            self::Text,
            self::Html,
            self::Json,
            self::Wysiwyg => \Doctrine\DBAL\Types\Types::TEXT,
        };
    }

    public function getDefaultValue(): int|bool|string
    {
        return match ($this) {
            self::Int => 0,
            self::Bool => false,
            self::String => '',
            self::Text => '',
            self::Html => '',
            self::Json => '',
            self::Wysiwyg => '',
        };
    }

    public function isStringable(): bool
    {
        return in_array($this, [
            self::String,
            self::Text,
            self::Html,
            self::Json,
            self::Wysiwyg,
        ]);
    }

    public function isValidValue(mixed $value): bool
    {
        return match ($this) {
            self::Int => is_int($value),
            self::Bool => is_bool($value),
            self::Json => is_array($value) || is_object($value), // JSON is a bare string also, in some cases !!!
            self::String,
            self::Text,
            self::Html,
            self::Wysiwyg  => is_string($value),
//            default => false, // This should never happen
        };
    }

    /**
     * @return array<string>
     */
    public static function allowedTypes(): array
    {
        $allowedTypes = [];
        foreach (self::cases() as $case) {
            $allowedTypes[] = $case->value;
        }

        return $allowedTypes;
    }
}
