<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\Utils\DateTime;
use samdark\sitemap\Sitemap;
use Tracy\Debugger;

final class SitemapGenerator extends BaseModel
{
    public const SITEMAP_PATH = WWW_DIR . '/sitemap.xml';
    private const DEFAULT_TTL_SECONDS = 3600;

    public function __construct(
        protected Explorer $db,
        protected ConfigManager $configManager,
        protected TranslationManager $translationManager,
        protected UrlGenerator $urlGenerator
    ) {}

    /**
     * Generates a sitemap.xml file with URLs of published articles.
     *
     * This method retrieves all published articles from the database,
     * constructs their URLs using the UrlGenerator, and writes them to
     * a sitemap file.
     *
     * The sitemap is saved to the predefined path defined in `SITEMAP_PATH`.
     */
    public function generate(): void
    {
        $sitemap = new Sitemap(self::SITEMAP_PATH);

        $articles = $this->db->table('article')
            ->where('published', 1)
            ->order('updated_at DESC')
            ->fetchAll();

        foreach ($articles as $article) {
            $sitemap->addItem(
                HOME_URL . $this->urlGenerator->generateArticleUrl($article['id']),
                DateTime::from($article['updated_at'])->getTimestamp(),
                Sitemap::DAILY,
                '0.8'
            );
        }

        $sitemap->write();
    }

    /**
     * Regenerates the sitemap if it does not exist or is older than the configured TTL.
     *
     * @param bool $forced If true, forces regeneration regardless of the file's existence or age.
     * @return bool Returns true if the sitemap was regenerated, false otherwise.
     */
    public function regenerate(bool $forced = false): bool
    {
        if ($forced || !file_exists(self::SITEMAP_PATH) || !Debugger::$productionMode) {
            $this->generate();
            return true;
        }

        $ttl = $this->c('SITEMAP_TTL_SECONDS') ?? self::DEFAULT_TTL_SECONDS;

        if (time() - filemtime(self::SITEMAP_PATH) > $ttl) {
            $this->generate();
            return true;
        }

        return false;
    }

    /**
     * Retrieves the content of the sitemap.xml file.
     *
     * @return string The content of the sitemap file.
     * @throws \RuntimeException If the sitemap file does not exist or cannot be read.
     */
    public function getContent(): string
    {
        if (!is_file(self::SITEMAP_PATH)) {
            throw new \RuntimeException("Sitemap file does not exist.");
        }

        $content = file_get_contents(self::SITEMAP_PATH);

        if ($content === false) {
            throw new \RuntimeException("Failed to read sitemap file.");
        }

        return $content;
    }
}
