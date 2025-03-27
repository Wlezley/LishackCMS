<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IArticleEditorFactory;
use App\Models\ArticleException;
use App\Models\ArticleManager;
use App\Models\UserManager;

class ArticlePresenter extends SecuredPresenter
{
    /** @var ArticleManager @inject */
    public ArticleManager $articleManager;

    /** @var UserManager @inject */
    public UserManager $userManager;

    /** @var IArticleEditorFactory @inject */
    public IArticleEditorFactory $articleEditor;

    public function renderDefault(): void
    {
        $this->template->articleList = [];
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(?int $id): void
    {
        if (!$id) {
            $this->redirect(':create');
        }

        $this->template->title .= " ID: $id";

        try {
            $this->template->article = $this->articleManager->getById($id);
        } catch (ArticleException $e) {
            $this->flashMessage("Článek $id nebyl nalezen.", 'danger');
            $this->redirect('Article:');
        }

        try {
            $this->template->articleURL = $this->articleManager->generateUrl($id);
        } catch (ArticleException $e) {
            $this->template->notInCategoryError = $e->getMessage();
        }
    }

    public function actionDelete(int $id): void
    {
        try {
            $this->articleManager->getById($id);
        } catch (\Exception $e) {
            $this->flashMessage("Článek ID: $id nebyl nalezen.", 'danger');
            $this->redirect('Article:');
        }

        $this->flashMessage("Článek ID: $id byl odstraněn.", 'info');
        $this->redirect('Article:');
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentArticleEditor(): \App\Components\Admin\ArticleEditor
    {
        $form = $this->articleEditor->create();
        $id = (int) $this->getParameter('id');

        $form->setArticleManager($this->articleManager);
        $form->setUserManager($this->userManager);

        if ($id) {
            try {
                $articleData = $this->articleManager->getById($id);
                $form->setParam($articleData);
                $form->setOrigin($form::OriginEdit);
            } catch (\Exception $e) {
                $this->flashMessage('Chyba při čtení dat článku: ' . $e->getMessage(), 'danger');
            }
        } else {
            $form->setParam($this->getHttpRequest()->getPost('param'));
            $form->setOrigin($form::OriginCreate);
        }

        $form->onSuccess = function(string $message, $articleId): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Article:edit', ['id' => $articleId]);
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }
}
