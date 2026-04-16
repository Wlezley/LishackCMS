<?php

declare(strict_types=1);

namespace App\Components\Admin\ArticleList;

interface IArticleListFactory
{
    public function create(): ArticleList;
}
