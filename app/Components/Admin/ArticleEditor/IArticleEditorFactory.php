<?php

declare(strict_types=1);

namespace App\Components\Admin\ArticleEditor;

interface IArticleEditorFactory
{
    public function create(): ArticleEditor;
}
