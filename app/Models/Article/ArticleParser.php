<?php

declare(strict_types=1);

namespace App\Models\Article;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Webmozart\Assert\Assert;

class ArticleParser
{
    public function __construct(
        private Presenter $presenter
    ) {
    }

    /**
     * @param string $text Catch the component tag in the format "<nette-component param-PARAMNAME="VALUE">COMPONENT_NAME</nette-component>"
     */
    public function parseComponents(string $text): string
    {
        $parsedBlock = preg_replace_callback(
            '#<nette-component([^>]*)>(.*?)</nette-component>#is',
            fn(array $matches) => $this->processTag($matches[1], trim($matches[2])),
            $text
        );
        Assert::string($parsedBlock, 'Failed to parse components');
        return $parsedBlock;
    }

    private function processTag(string $attributeString, string $fallbackName): string
    {
        $attrs = $this->parseAttributes($attributeString);

        $name = $attrs['name'] ?? $fallbackName;
        unset($attrs['name']);

        $params = [];
        foreach ($attrs as $key => $val) {
            if (str_starts_with($key, 'param-')) {
                $params[substr($key, 6)] = $val;
            }
        }

        return $this->renderComponent($name, $params);
    }

    /**
     * @return array<string,string>
     */
    private function parseAttributes(string $attributeString): array
    {
        $attributes = [];
        preg_match_all('/([a-zA-Z0-9_\-]+)="([^"]*)"/', $attributeString, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }
        return $attributes;
    }

    /**
     * @param array<string,mixed> $params
     */
    public function renderComponent(string $name, array $params = []): string
    {
        $component = $this->presenter->getComponent($name, false);

        if (!$component instanceof Control) {
            return "<!-- Component '{$name}' not found or is not a Control -->";
        }

        try {
            $template = $component->template;
            $template->setParameters($params);

            bdump($params, "Component '{$name}' Params");

            $templatePath = '';
            if (method_exists($component, 'getTemplatePath')) {
                $templatePath = $component->getTemplatePath();
            }

            if (!$templatePath) {
                $reflection = new \ReflectionClass($component);

                $componentFile = $reflection->getFileName();
                Assert::string($componentFile, 'Failed to get component file path');

                $componentName = $component->getName();
                Assert::string($componentName, 'Failed to get component name');

                $templatePath = dirname($componentFile) . '/' . $componentName . '.latte';
            }

            bdump($templatePath, "Component '{$name}' Template Path");

            if (is_file($templatePath)) {
//                Assert::methodExists($template, 'setFile', 'Unable to set a template file for a component');

                if (method_exists($template, 'setFile')) {
                    $template->setFile($templatePath);
                }
            } else {
                return "<!-- Template file not found for '{$name}' -->";
            }

            if (method_exists($template, 'renderToString')) {
                return $template->renderToString();
            } else {
                return "<!-- Component '{$name}' render failed -->";
            }
        } catch (\Throwable $e) {
            return "<!-- Cannot access template: {$e->getMessage()} -->";
        }
    }
}
