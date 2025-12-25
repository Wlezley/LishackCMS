<?php

declare(strict_types=1);

namespace App\Models;

use App\Exception\TranslationException;
use Nette\Database\Explorer;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class TranslationLanguage
{
    public const string TABLE_NAME = 'lang';

    /** @var array<string,array<string,mixed>> */
    private array $languages = [];

    public function __construct(
        private readonly Explorer $db
    ) {
        $this->load();
    }

    private function load(): void
    {
        if (empty($this->languages)) {
            foreach ($this->db->table(self::TABLE_NAME)->fetchAll() as $row) {
                $item = $row->toArray();
                $key = $item['lang'];
                $this->languages[$key] = $item;
            }
        }
    }

    public function reload(): void
    {
        $this->languages = [];
        $this->load();
    }

    /**
     * @throws TranslationException If language is not found.
     */
    public function checkLanguage(string $lang): void
    {
        try {
            Assert::keyExists($this->languages, $lang, "Language '$lang' not found.");
        } catch (InvalidArgumentException $e) {
            throw new TranslationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array<string,mixed>
     * @throws TranslationException If language is not found.
     */
    public function getLanguage(string $lang): array
    {
        $this->checkLanguage($lang);
        return $this->languages[$lang];
    }

    /** @return array<string,array<string,mixed>> */
    public function getList(bool $enabledOnly = true): array
    {
        return $enabledOnly
            ? array_filter($this->languages, fn($lang) => $lang['enabled'] ?? false)
            : $this->languages;
    }

    /** @return array<string,string> */
    public function getNames(bool $enabledOnly = true): array
    {
        $names = [];
        $languages = $this->getList($enabledOnly);

        foreach ($languages as $key => $data) {
            $names[$key] = $data['name'];
        }

        return $names;
    }

    public function getDefaultLang(?string $fallback = null): string
    {
        foreach ($this->languages as $lang => $data) {
            if ($data['default'] == 1) {
                return $lang;
            }
        }

        Assert::notNull($fallback, 'No default language found');

        return $fallback;
    }

    public function getSecondaryLang(?string $fallback = null): string
    {
        foreach ($this->languages as $lang => $data) {
            if ($data['default'] == 0 && $data['enabled'] == 1) {
                return $lang;
            }
        }

        Assert::notNull($fallback, 'No secondary language found');

        return $fallback;
    }
}
