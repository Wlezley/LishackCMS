<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;


class ArticlePresenter extends _SecuredPresenter
{
    public function renderDefault()
    {
        $this->template->title = 'Seznam článků';
        $this->template->articleList = [];
    }

    public function renderCreate()
    {
        $this->template->title = 'Vytvoření nového článku';
    }

    public function renderEdit(int $id = 0)
    {
        $this->template->title = "Editace článku ID: $id";
        $this->template->articleId = $id;
    }

    public function actionDelete(int $id)
    {
        $this->flashMessage("Článek ID: $id byl odstraněn.", 'info');
        $this->redirect('Article:');
    }
}
