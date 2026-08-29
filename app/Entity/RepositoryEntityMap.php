<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleRepository;
use App\Entity\Category\Category;
use App\Entity\Category\CategoryRepository;
use App\Entity\CmsConfig\CmsConfig;
use App\Entity\CmsConfig\CmsConfigRepository;
use App\Entity\Dataset\Dataset;
use App\Entity\Dataset\DatasetRepository;
use App\Entity\DatasetColumn\DatasetColumn;
use App\Entity\DatasetColumn\DatasetColumnRepository;
use App\Entity\Language\Language;
use App\Entity\Language\LanguageRepository;
use App\Entity\Redirect\Redirect;
use App\Entity\Redirect\RedirectRepository;
use App\Entity\SmsLog\SmsLog;
use App\Entity\SmsLog\SmsLogRepository;
use App\Entity\StorageFiles\StorageFiles;
use App\Entity\StorageFiles\StorageFilesRepository;
use App\Entity\StorageTree\StorageTree;
use App\Entity\StorageTree\StorageTreeRepository;
use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationRepository;
use App\Entity\TranslationLog\TranslationLog;
use App\Entity\TranslationLog\TranslationLogRepository;
use App\Entity\User\User;
use App\Entity\User\UserRepository;

final class RepositoryEntityMap
{
    /**
     * @var array<class-string, class-string>
     */
    private const array MAP = [
        ArticleRepository::class => Article::class,
        CategoryRepository::class => Category::class,
        CmsConfigRepository::class => CmsConfig::class,
        DatasetRepository::class => Dataset::class,
        DatasetColumnRepository::class => DatasetColumn::class,
        LanguageRepository::class => Language::class,
        RedirectRepository::class => Redirect::class,
        SmsLogRepository::class => SmsLog::class,
        StorageFilesRepository::class => StorageFiles::class,
        StorageTreeRepository::class => StorageTree::class,
        TranslationRepository::class => Translation::class,
        TranslationLogRepository::class => TranslationLog::class,
        UserRepository::class => User::class,
    ];

    /**
     * @param class-string $repositoryClass
     * @return class-string
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
