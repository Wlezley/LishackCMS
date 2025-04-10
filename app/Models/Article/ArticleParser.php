<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

class ArticleParser
{
    public function __construct(
        private Presenter $presenter
    ) {}

    /**
     * @param string $text Catch the component tag in the format "<nette-component param-PARAMNAME="VALUE">COMPONENT_NAME</nette-component>"
     * @return string
     */
    public function parseComponents(string $text): string
    {
        return preg_replace_callback(
            '#<nette-component([^>]*)>(.*?)</nette-component>#is',
            fn(array $matches) => $this->processTag($matches[1], trim($matches[2])),
            $text
        );
    }

    /**
     * @param string $attributeString
     * @param string $fallbackName
     * @return string
     */
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
     * @param string $attributeString
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
     * @param string $name
     * @param array<string,mixed> $params
     * @return string
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
                $templatePath = dirname($reflection->getFileName()) . '/' . $component->getName() . '.latte';
            }

            bdump($templatePath, "Component '{$name}' Template Path");

            if (is_file($templatePath)) {
                $template->setFile($templatePath);
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
