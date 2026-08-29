<?php

declare(strict_types=1);

namespace App\Entity\Translation;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Translation>
 */
final readonly class TranslationRepository extends BaseRepository implements TranslationRepositoryInterface
{
}
