<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\DatasetSidebar\IDatasetSidebarFactory;
use App\Components\Pagination\IPaginationFactory;
use App\Models\User\UserManager;
use App\Models\User\UserRole;
use Webmozart\Assert\Assert;

class SecuredPresenter extends BasePresenter
{
    protected UserRole $userRole;

    /** @var IDatasetSidebarFactory @inject */
    public IDatasetSidebarFactory $datasetSidebarFactory;

    /** @var IPaginationFactory @inject */
    public IPaginationFactory $paginationFactory;

    // Pagination
    private ?int $itemsPerPage = null;
    private ?int $totalItems = null;

    public function startup(): void
    {
        parent::startup();

        if (!$this->user->isLoggedIn() && $this->presenter->getName() !== 'Admin:Sign') {
            if ($this->isAjax()) {
                $this->flashMessage('Přístup odepřen: Uživatel se odhlásil', 'danger');
            }

            $this->redirect('Sign:in');
        }

        if ($this->user->isLoggedIn()) {
            $userData = $this->db->table(UserManager::TABLE_NAME)->select('deleted, enabled, role')->where([
                'id' => $this->user->getId(),
            ])->fetch();

            if (!$userData || $userData['deleted'] == 1 || $userData['enabled'] != 1) {
                $this->user->logout(true);
                $this->flashMessage('Uživatel byl odhlášen', 'danger');
                $this->redirect('Sign:in');
            }

            if ($this->user->getIdentity()?->getData()['role'] !== $userData['role']) {
                $this->user->logout(true);
                $this->flashMessage('Uživatel byl odhlášen: Změna role', 'danger');
                $this->redirect('Sign:in');
            }
        }

        $this->userRole = new UserRole($this->user);
    }

    public function afterRender(): void
    {
        parent::afterRender();

        try {
            $userIdentity = $this->getUser()->getIdentity();
            Assert::notNull($userIdentity, 'User identity is null.');

            $this->template->userData = $userIdentity->getData();
//            $this->getTemplate()->userData = $userIdentity->getData(); // TODO: Check if this works, instead of above
        } catch (\Exception $e) {
            // TODO: Log error or handle it gracefully
        }
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentDatasetSidebar(): \App\Components\Admin\DatasetSidebar\DatasetSidebar
    {
        $control = $this->datasetSidebarFactory->create();

        return $control;
    }

    // ##########################################
    // ###             PAGINATION             ###
    // ##########################################

    protected function setPagination(int $itemsPerPage, int $totalItems): void
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->totalItems = $totalItems;
    }

    protected function createComponentPagination(): \App\Components\Pagination\Pagination
    {
        if ($this->itemsPerPage === null || $this->totalItems === null) {
            throw new \LogicException('Call setPagination() in the render method first.');
        }

        $control = $this->paginationFactory->create();
        $control->setQueryParams($this->getHttpRequest()->getQuery());
        $control->setItemsPerPage($this->itemsPerPage);
        $control->setTotalItems($this->totalItems);
        $control->setCurrentPage((int) $this->getParameter('page', 1));

        return $control;
    }
}
