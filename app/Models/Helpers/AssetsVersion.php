<?php

declare(strict_types=1);

namespace App\Models\Helpers;

use Nette\Bridges\ApplicationLatte\Template;

class AssetsVersion
{
    private Template $template;

    private string $basePath = '';

    public function setTemplate(Template $template): self
    {
        $this->template = $template;
        return $this;
    }

    public function setBasePath(string $basePath): self
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function addFile(string $fileName, string $descriptor): self
    {
        if (!isset($this->template)) {
            throw new \Exception('Template was not set. First set the template using the setTemplate(Template $template) method.');
        }

        $this->template->$descriptor = self::getAssetVersion($this->basePath . $fileName);
        return $this;
    }

    public static function getAssetVersion(string $fileName): int
    {
        $version = false;

        if (file_exists($fileName)) {
            $version = filemtime($fileName);
        }

        return $version ? $version : 0;
    }
}
