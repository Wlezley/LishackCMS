<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\RedirectForm\IRedirectFormFactory;
use App\Components\Admin\RedirectForm\RedirectForm;
use App\Components\Admin\RedirectList\IRedirectListFactory;
use App\Components\Admin\RedirectList\RedirectList;
use App\Entity\Redirect\RedirectRepositoryInterface;
use App\Service\Redirect\RedirectService;

class RedirectPresenter extends SecuredPresenter
{
    /** @var RedirectRepositoryInterface @inject */
    public RedirectRepositoryInterface $redirectRepository;

    /** @var RedirectService @inject */
    public RedirectService $redirectService;

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

        if ($this->redirectRepository->findById((int) $id) === null) {
            $this->flashMessage(
                message: $this->tf('redirect.id.not-found', (int) $id),
                type: 'danger',
            );

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

        $redirect = $this->redirectRepository->findById((int) $data['id']);
        if ($redirect === null) {
            // TODO: Flash message about error
            return;
        }

        $this->redirectService->delete($redirect);
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentRedirectList(): RedirectList
    {
        $control = $this->redirectList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }

    protected function createComponentRedirectForm(): RedirectForm
    {
        $form = $this->redirectForm->create();
        $id = $this->getParameter('id');

        if ($id) {
            $form->setOrigin($form::OriginEdit);

            $redirect = $this->redirectRepository->findById((int) $id);
            if ($redirect === null) {
                $this->flashMessage(
                    message: $this->tf('redirect.id.not-found', (int) $id),
                    type: 'danger',
                );

                return $form;
            }

            /** @var array<string, int|string> $param */
            $param = [
                'id' => $redirect->getId(),
                'source' => $redirect->getSource(),
                'target' => $redirect->getTarget(),
                'code' => $redirect->getCode()->value,
                'enabled' => $redirect->isEnabled(),
                'page' => $this->getHttpRequest()->getQuery('page'),
            ];

            $form->setParam($param);
        } else {
            $form->setOrigin($form::OriginCreate);
            $form->setParam($this->getHttpRequest()->getPost('param'));
        }

        $form->onSuccess = function (string $message, int $page): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Redirect:', ['page' => $page]);
        };

        $form->onError = function (string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }
}
