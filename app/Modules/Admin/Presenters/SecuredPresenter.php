<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\DatasetSidebar\DatasetSidebar;
use App\Components\Admin\DatasetSidebar\IDatasetSidebarFactory;
use App\Components\Pagination\IPaginationFactory;
use App\Components\Pagination\Pagination;
use App\Entity\User\UserRepositoryInterface;
use App\Models\User\UserRole;
use Webmozart\Assert\Assert;

class SecuredPresenter extends BasePresenter
{
    protected UserRole $userRole;

    /** @var IDatasetSidebarFactory @inject */
    public IDatasetSidebarFactory $datasetSidebarFactory;

    /** @var IPaginationFactory @inject */
    public IPaginationFactory $paginationFactory;

    /** @var UserRepositoryInterface @inject */
    public UserRepositoryInterface $userRepository;

    // Pagination
    private ?int $itemsPerPage = null;
    private ?int $totalItems = null;

    public function startup(): void
    {
        parent::startup();

        // TODO: TRANSLATE FLASH MESSAGES !!!
        if (!$this->user->isLoggedIn() && $this->presenter->getName() !== 'Admin:Sign') {
            if ($this->isAjax()) {
                $this->flashMessage('Přístup odepřen: Uživatel se odhlásil', 'danger');
            }

            $this->redirect('Sign:in');
        }

        if ($this->user->isLoggedIn()) {
            $userData = $this->userRepository->getById((int) $this->user->getId()); // TODO: remove re-typecast... It's INSECURE as f*ck HERE !!!

            if ($userData === null || $userData->isDeleted() || !$userData->isEnabled()) {
                $this->user->logout(true);
                $this->flashMessage('Uživatel byl odhlášen', 'danger');
                $this->redirect('Sign:in');
            }

            if ($this->user->getIdentity()?->getData()['role'] !== $userData->getRole()->value) {
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

    protected function createComponentDatasetSidebar(): DatasetSidebar
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

    protected function createComponentPagination(): Pagination
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
