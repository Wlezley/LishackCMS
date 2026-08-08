<?php

declare(strict_types=1);

namespace App\Doctrine;

final class SchemaAssetsFilter
{
    public function __invoke(string $assetName): bool
    {
        return !preg_match(
            '~^(?:_phinxlog|dataset_data_[0-9]+)$~',
            $assetName
        );
    }
}
