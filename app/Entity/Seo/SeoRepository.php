<?php

declare(strict_types=1);

namespace App\Entity\Seo;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Seo>
 */
final readonly class SeoRepository extends BaseRepository implements SeoRepositoryInterface
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
    ): Seo {
        $seo = new Seo(
            robots: $robots,
            canonicalUrl: $canonicalUrl,
            ogTitle: $ogTitle,
            ogDescription: $ogDescription,
            ogImage: $ogImage,
            ogUrl: $ogUrl,
            ogType: $ogType,
            metaTitle: $metaTitle,
            metaDescription: $metaDescription,
            metaKeywords: $metaKeywords,
        );

        $this->save($seo);

        return $seo;
    }

//    // TODO: Move to the SEO service
//    public function assign(
//        Article | Category $entity,
//        Seo $seo,
//    ): void {
//        $entity->setSeo($seo);
//        // Save entity?
//    }
//
//    // TODO: Move to the SEO service
//    public function assignBySeoId(
//        Article | Category $entity,
//        int $seoId,
//    ): void {
//        $seo = $this->findById($seoId);
//        $entity->setSeo($seo);
//        // Save entity?
//    }
//
//    // TODO: Move to the SEO service
//    public function resetSeo(Article | Category $entity): void
//    {
//        $entity->setSeo(null);
//        // Save entity?
//    }
}
