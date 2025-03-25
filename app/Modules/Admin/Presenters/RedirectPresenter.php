<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IRedirectFormFactory;
use App\Components\Admin\IRedirectListFactory;
use App\Models\RedirectManager;

class RedirectPresenter extends SecuredPresenter
{
    /** @var RedirectManager @inject */
    public RedirectManager $redirectManager;

    /** @var IRedirectListFactory @inject */
    public IRedirectListFactory $redirectList;

    /** @var IRedirectFormFactory @inject */
    public IRedirectFormFactory $redirectForm;

    public function renderDefault(int $page = 1, ?string $search = null): void
    {
        $this->template->search = $search;
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(?string $id): void
    {
        if (!$id) {
            $this->redirect(':default');
        }
    }

    public function renderImport(): void
    {
    }

    public function renderExport(): void
    {
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        $this->redirectManager->delete($data['id']);
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

    protected function createComponentRedirectForm(): \App\Components\Admin\RedirectForm
    {
        $form = $this->redirectForm->create();
        $id = $this->getParameter('id');

        if ($id) {
            $form->setOrigin($form::OriginEdit);
            $param = $this->redirectManager->getRow($id);

            if ($param) {
                $param['page'] = $this->getHttpRequest()->getQuery('page');
                $form->setParam($param);
            } else {
                $this->flashMessage('Přesměrování nebylo nalezeno');
            }
        } else {
            $form->setOrigin($form::OriginCreate);
            $form->setParam($this->getHttpRequest()->getPost('param'));
        }

        $form->onSuccess = function(string $message, int $page): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Redirect:', ['page' => $page]);
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }
}
