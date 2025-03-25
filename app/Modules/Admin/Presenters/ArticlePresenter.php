<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class ArticlePresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->template->articleList = [];
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(int $id = 0): void
    {
        $this->template->title .= " ID: $id";
        $this->template->articleId = $id;
    }

    public function actionDelete(int $id): void
    {
        $this->flashMessage("Článek ID: $id byl odstraněn.", 'info');
        $this->redirect('Article:');
    }
}
