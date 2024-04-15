<?php

declare(strict_types=1);

namespace App\Modules\Ajax\Presenters;

use Nette;
use Tracy\Debugger;

final class AjaxPresenter extends Nette\Application\UI\Presenter
{
    public function __construct()
    {
        Debugger::$showBar = false;
    }

    public function beforeRender(): void
    {
        $this->template->setFile(__DIR__ . '/../Templates/default.latte');

        if (!isset($this->template->render)) {
            $this->template->render = '';
        }
    }

    public function actionDefault(): void
    {
        $this->template->render = 'Default AJAX action';
    }

    public function actionPing(): void
    {
        $this->template->render = 'AJAX PONG';
    }
}
