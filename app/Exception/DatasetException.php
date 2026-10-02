<?php

declare(strict_types=1);

namespace App\Exception;

class DatasetException extends \Exception
{
    public static function datasetIdNotFound(int $id): self
    {
        return new self("Dataset id '$id' not found");
    }

    public static function datasetColumnsNotFound(int $id): self
    {
        return new self("Dataset columns for dataset id '$id' not found");
    }
}
