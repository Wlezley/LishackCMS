<?php

declare(strict_types=1);

namespace App\Models;

class ArticleException extends \Exception
{
    /** @var string $categoryUrl */
    private string $categoryUrl;

    public function setCategoryUrl(string $categoryUrl): void
    {
        $this->categoryUrl = $categoryUrl;
    }

    public function getCategoryUrl(): string
    {
        return $this->categoryUrl;
    }
}
