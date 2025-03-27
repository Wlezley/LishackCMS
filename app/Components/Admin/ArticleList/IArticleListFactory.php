<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IArticleListFactory
{
    public function create(): ArticleList;
}
