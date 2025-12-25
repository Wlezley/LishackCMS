<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\StorageSystem\TreeManager;
use App\Models\UserManager;
use Nette\Utils\Json;

class FileExplorer extends BaseControl
{
    public function __construct(
        private UserManager $userManager,
        private TreeManager $treeManager
    ) {}

    public function render(int $id = 0): void
    {
        $this->template->treeList = $this->treeManager->getChildFolders($id);
        bdump($this->template->treeList);

        $this->template->fileList = $this->treeManager->getAllFiles($id);
        bdump($this->template->fileList);

        $this->template->ownerList = $this->userManager->getList(true);
        bdump($this->template->ownerList);

        $this->template->getJsonTree = function($id, $treeName) {
            // TODO: Fix empty modal on second call of deletion method
            return Json::encode([
                'id' => (string)$id,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-folder', $treeName)
                ]
            ]);
        };

        $this->template->getJsonFile = function($id, $fileName) {
            // TODO: Fix empty modal on second call of deletion method
            return Json::encode([
                'id' => (string)$id,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-file', $fileName)
                ]
            ]);
        };

        $this->getTemplate()->setFile(__DIR__ . '/FileExplorer.latte');
        $this->getTemplate()->render();
    }

    public function handleEditFolder(string $id): void
    {
        bdump($id, "handleEditFolder ID");

        // $this->presenter->redirect('FileExplorer:editFolder', [
        //     'id' => $id
        // ]);
    }

    public function handleEditFile(string $id): void
    {
        bdump($id, "handleEditFile ID");

        // $this->presenter->redirect('FileExplorer:editFile', [
        //     'id' => $id
        // ]);
    }

    public function handleDeleteFolder(): void
    {
        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        }

        $data = $this->presenter->getHttpRequest()->getPost();
        bdump($data);

        // $this->presenter->redirect('FileExplorer:deleteFolder', [
        //     'id' => $id
        // ]);
    }

    public function handleDeleteFile(): void
    {
        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        }

        $data = $this->presenter->getHttpRequest()->getPost();
        bdump($data);

        // $this->presenter->redirect('FileExplorer:deleteFile', [
        //     'id' => $id
        // ]);
    }
}
