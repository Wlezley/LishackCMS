<?php

declare(strict_types=1);

namespace App\Entity\Seo;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<Seo>
 */
interface SeoRepositoryInterface extends BaseRepositoryInterface
{
    public function create(
        ?string $robots = null,
        ?string $canonicalUrl = null,
        ?string $ogTitle = null,
        ?string $ogDescription = null,
        ?string $ogImage = null,
        ?string $ogUrl = null,
        ?string $ogType = null,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?string $metaKeywords = null,
    ): Seo;
}
