<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IRedirectListFactory;
use App\Models\RedirectManager;

class RedirectPresenter extends SecuredPresenter
{
    /** @var RedirectManager @inject */
    public RedirectManager $redirectManager;

    /** @var IRedirectListFactory @inject */
    public IRedirectListFactory $redirectList;

    public function renderDefault(int $page = 1, ?string $search = null): void
    {
        $this->template->title = 'Přesměrování';

        $this->template->search = $search;
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Vytvořit přesměrování';
    }

    public function renderEdit(string $source): void
    {
        $this->template->title = 'Editace přesměrování';
        $this->template->source = $source;
    }

    public function renderImport(): void
    {
        $this->template->title = 'Import přesměrování';
    }

    public function renderExport(): void
    {
        $this->template->title = 'Export přesměrování';
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        $this->redirectManager->delete($data['source']);
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentRedirectList(): \App\Components\Admin\RedirectList
    {
        $control = $this->redirectList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }
}
