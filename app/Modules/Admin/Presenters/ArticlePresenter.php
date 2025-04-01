<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IArticleEditorFactory;
use App\Components\Admin\IArticleListFactory;
use App\Models\ArticleException;
use App\Models\ArticleManager;
use App\Models\CategoryManager;
use App\Models\UserManager;

class ArticlePresenter extends SecuredPresenter
{
    /** @var ArticleManager @inject */
    public ArticleManager $articleManager;

    /** @var CategoryManager @inject */
    public CategoryManager $categoryManager;

    /** @var UserManager @inject */
    public UserManager $userManager;

    /** @var IArticleListFactory @inject */
    public IArticleListFactory $articleList;

    /** @var IArticleEditorFactory @inject */
    public IArticleEditorFactory $articleEditor;

    public function renderDefault(int $page = 1, ?string $search = null): void
    {
        $this->template->search = $search;
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
            $this->flashMessage($this->tf('article.id.not-found', $id), 'danger');
            $this->redirect('Article:');
        }

        try {
            $this->template->articleURL = $this->articleManager->generateUrl($id);
            if ($this->template->article['published'] != 1) {
                $this->template->articleURL .= '?preview=1';
            }
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

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        $this->articleManager->delete((int)$data['id']);
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentArticleList(): \App\Components\Admin\ArticleList
    {
        $control = $this->articleList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }

    protected function createComponentArticleEditor(): \App\Components\Admin\ArticleEditor
    {
        $form = $this->articleEditor->create();
        $id = (int) $this->getParameter('id');

        $form->setArticleManager($this->articleManager);
        $form->setCategoryManager($this->categoryManager);
        $form->setUserManager($this->userManager);

        if ($id) {
            try {
                $articleData = $this->articleManager->getById($id);
                $form->setParam($articleData);
                $form->setOrigin($form::OriginEdit);
            } catch (ArticleException $e) {
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
