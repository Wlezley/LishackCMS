<?php

declare(strict_types=1);

namespace App\Models;

use Latte\Loaders\StringLoader;
use Nette\Application\Helpers;
use Nette\Application\UI\Control;
use Nette\Application\UI\TemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;

class ArticleParser
{
    public function __construct(
        private TemplateFactory $templateFactory
    ) {}

    public function parseText(string $text, ?Control $presenter = null): string
    {
        if (empty($text)) {
            return $text;
        }

        $moduleName = Helpers::splitName($presenter->getName())[0];
        $template = $this->templateFactory->createTemplate($presenter);
        /** @var Template $template */
        $latte = $template->getLatte();
        $latte->setLoader(new StringLoader());

        return $text;
    }
}
