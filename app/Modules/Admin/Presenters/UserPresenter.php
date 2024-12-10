<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\UserManager;
use Ublaboo\DataGrid\DataGrid;

class UserPresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'Uživatelské účty';
        $this->template->userList = [];
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Vytvoření nového uživatele';
    }

    public function renderEdit(int $id = 0): void
    {
        $this->template->title = "Editace uživatele ID: $id";
        $this->template->userId = $id;
    }

    public function actionDelete(int $id): void
    {
        $this->flashMessage("Uživatel ID: $id byl odstraněn.", 'info');
        $this->redirect('User:');
    }

    public function createComponentUserList(): DataGrid
    {
        $grid = new DataGrid();

        $grid->setDataSource($this->db->table(UserManager::TABLE_NAME)->select('*'));

        $grid->setDefaultPerPage(20);
        $grid->setItemsPerPageList([20], false);


        $grid->addColumnText('id', 'Id')
            ->setSortable();

        $grid->addColumnText('name', 'Jméno')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('full_name', 'Celé jméno')
            ->setSortable()
            ->setFilterText();

        $columnRole = $grid->addColumnStatus('role', 'Oprávnění');
        $columnRole
            ->addOption('user', 'Uživatel')
            ->setClass('btn-info')
            ->endOption()
            ->addOption('admin', 'Správce')
            ->setClass('btn-warning')
            ->endOption()
            ->setSortable();

        $columnRole->onChange[] = [$this, 'changeStatus'];

        $grid->addColumnText('email', 'E-mail')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('deleted', 'Smazaný')
            ->setReplacement([
                0 => 'Ne',
                1 => 'Ano',
            ]);

        $grid->addColumnText('enabled', 'Povoleno')
            ->setReplacement([
                0 => 'Ne',
                1 => 'Ano',
            ]);

        $grid->addColumnDateTime('created', 'Vytvořeno')
            ->setFormat('j. n. Y');

        $grid->addColumnDateTime('last_login', 'Přihlášení')
            ->setFormat('j. n. Y H:m:s')
            ->setReplacement(['' => 'N/A']);
            // ->setRenderer(function($item) {return $item->last_login ?? 'N/A';});

        $grid->addAction(':edit', 'edit')
            ->setClass('btn btn-xs btn-success');

        // $grid->setColumnsHideable();

        return $grid;
    }
}
