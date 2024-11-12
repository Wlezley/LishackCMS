<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Models\Installer;
use Nette\Application\UI\Presenter;

final class InstallPresenter extends Presenter
{
    public function __construct(private Installer $installer)
    {
        parent::__construct();
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();

        if ($this->installer->isInstalled()) {
            $this->redirect('Website:default');
        }

        $this->layout = 'systemLayout';
    }

    public function renderRun(): void
    {
        $this->installer->run();
        $this->template->phinxLog = $this->installer->getLog();
    }
}
