<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;

class TranslationLanguage
{
    public const TABLE_NAME = 'lang';

    /** @var array<string,array<string,mixed>> */
    private array $languages = [];

    public function __construct(
        private Explorer $db
    ) {
        $this->load();
    }

    private function load(bool $reload = false): void
    {
        if ($reload) {
            $this->languages = [];
        }

        if (empty($this->languages)) {
            foreach ($this->db->table(self::TABLE_NAME)->fetchAll() as $row) {
                $languageRow = $row->toArray();
                $key = $languageRow['lang'];
                unset($languageRow['id'], $languageRow['lang']);
                $this->languages[$key] = $languageRow;
            }
        }
    }

    public function reload(): void
    {
        $this->load(true);
    }

    /** @return array<string,mixed>|null */
    public function getLanguage(string $lang): ?array
    {
        return $this->languages[$lang] ?? null;
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
        return array_column($this->getList($enabledOnly), 'name', 'lang');
    }

    public function getDefaultLang(?string $fallback = null): ?string
    {
        foreach ($this->languages as $lang => $data) {
            if ($data['default'] == 1) {
                return $lang;
            }
        }

        return $fallback;
    }
}
