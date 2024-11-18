<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\Config;
use Nette;

class _BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Explorer @inject */
    public $db;

    /** @var Config @inject */
    public $config;

    protected array $cmsConfig = [];

    public function startup(): void
    {
        parent::startup();

        // CMS config
        $this->cmsConfig = $this->config->getValues();
    }

    public function afterRender(): void
    {
        parent::afterRender();

        // CMS config
        $this->template->setParameters($this->cmsConfig);
        $this->template->VERSION = VERSION;
        $this->template->DEFAULT_LANG = DEFAULT_LANG;
        $this->template->DEFAULT_LANG_ADMIN = DEFAULT_LANG;

        // Assets version
        if (file_exists(ASSETS_DIR . "admin/script.js")) {
            $this->template->js_version = filemtime(ASSETS_DIR . "admin/script.js");
        }
        if (file_exists(ASSETS_DIR . "admin/style.css")) {
            $this->template->css_version = filemtime(ASSETS_DIR . "admin/style.css");
        }
    }
}
