<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use Nette;


class _BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Explorer @inject */
    public $db;


    public function afterRender(): void
    {
        parent::afterRender();

        $this->template->VERSION = '0.1a';
    }
}
