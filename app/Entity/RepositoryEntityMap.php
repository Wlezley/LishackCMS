<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Language\Language;
use App\Entity\Language\LanguageRepository;
use App\Entity\Redirect\Redirect;
use App\Entity\Redirect\RedirectRepository;

final class RepositoryEntityMap
{
    /**
     * @var array<class-string<BaseRepository<*>>, class-string<BaseEntity>>
     */
    private const array MAP = [
        LanguageRepository::class => Language::class,
        RedirectRepository::class => Redirect::class,
    ];

    /**
     * @param class-string<BaseRepository<*>> $repositoryClass
     * @return class-string<BaseEntity>
     */
    public static function resolveEntityClass(string $repositoryClass): string
    {
        if (!isset(self::MAP[$repositoryClass])) {
            throw new \LogicException(
                sprintf(
                    'No entity mapping exists for repository "%s".',
                    $repositoryClass,
                ),
            );
        }

        return self::MAP[$repositoryClass];
    }
}
