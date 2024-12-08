<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class ArticlePresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'Seznam článků';
        $this->template->articleList = [];
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Vytvoření nového článku';
    }

    public function renderEdit(int $id = 0): void
    {
        $this->template->title = "Editace článku ID: $id";
        $this->template->articleId = $id;
    }

    public function actionDelete(int $id): void
    {
        $this->flashMessage("Článek ID: $id byl odstraněn.", 'info');
        $this->redirect('Article:');
    }
}
