<?php

declare(strict_types=1);

namespace App\Modules\Cron\Presenters;

use Nette;
use Tracy\Debugger;

final class CronPresenter extends Nette\Application\UI\Presenter
{
    public function __construct()
    {
        set_time_limit(1200);
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
        $this->template->render = 'Default CRON action';

        // $this->terminate();
    }

    public function actionPing(): void
    {
        $this->template->render = 'CRON PONG';

        // $this->terminate();
    }
}
