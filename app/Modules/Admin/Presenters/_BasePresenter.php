<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\Config;
use App\Models\Helpers\AssetsVersion;
use Nette;
use Nette\Database\Explorer;

class _BasePresenter extends Nette\Application\UI\Presenter
{
    protected array $cmsConfig = [];

    public function __construct(
        protected Explorer $db,
        protected Config $config,
        private AssetsVersion $assetsVersion
    ) {}

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
        $this->template->HTML_LANG = (DEFAULT_LANG == 'cz' ? 'cs' : DEFAULT_LANG);
        $this->template->DEFAULT_LANG = DEFAULT_LANG;
        $this->template->DEFAULT_LANG_ADMIN = DEFAULT_LANG;
        $this->template->DEFAULT_LANG_TINYMCE = (DEFAULT_LANG == 'cz' ? 'cs' : DEFAULT_LANG);

        // Assets version
        $this->assetsVersion
            ->setTemplate($this->template)
            ->setBasePath(ASSETS_DIR)
            ->addFile('admin/dist/scripts.min.js', 'js_version')
            ->addFile('admin/dist/styles.css', 'css_version')
            ->addFile('tinymce-bundle/dist/scripts.min.js', 'js_version_tinymce')
            ->addFile('tinymce-bundle/dist/styles.css', 'css_version_tinymce');
    }
}
