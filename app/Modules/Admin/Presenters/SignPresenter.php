<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;


use Nette;

use App\Models\Authenticator;



final class SignPresenter extends Nette\Application\UI\Presenter
{
    private $auth;

    public function __construct(Authenticator $auth)
    {
        $this->auth = $auth;
    }

    function renderIn(): void
    {
        // $this->auth ...
        // TODO: SignIn FORM
        // ...
    }
}
