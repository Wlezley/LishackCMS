<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Models\Installer;
use Nette\Application\UI\Presenter;

final class InstallPresenter extends Presenter
{
    public function __construct(
        private Installer $installer
    ) {
        parent::__construct();
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();

        if ($this->installer->isInstalled()) {
            $this->redirect('Article:default');
        }

        $this->layout = 'systemLayout';
    }

    public function renderRun(): void
    {
        try {
            $this->installer->run();
        } catch (\Exception $e) {
            $this->redirect('Install:failed', [
                'error' => $e->getMessage()
            ]);
        }

        $this->template->adminHomeUrl = ADMIN_HOME_URL;
        $this->template->phinxLog = $this->installer->getLog();
    }

    public function renderFailed(string $error = ''): void
    {
        $this->template->error = $error;
    }
}
