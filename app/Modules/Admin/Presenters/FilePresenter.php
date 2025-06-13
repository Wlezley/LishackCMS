<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\StorageSystem\FileManager;
use Nette\Application\Responses\FileResponse;

class FilePresenter extends SecuredPresenter
{
    public function __construct(
        private FileManager $fileManager
    ) {
        // \Tracy\Debugger::$showBar = false;
    }

    public function actionDefault(): void
    {
        $this->terminate();
    }

    public function actionShow(int $id): void
    {
        // $fileName = $imageMetadata['filename'];
        // $absoluteFilePath = $imageMetadata['filepath'];
        // $contentType = mime_content_type($absoluteFilePath);

        $fileMeta = $this->fileManager->getFileById($id);
        $path = ''; // TODO !!!
        $name = $fileMeta->name;
        $type = $fileMeta->type;

        $response = new FileResponse($path, $name, $type, false);
        $this->sendResponse($response);
    }

    public function actionDownload(): void
    {
        $this->terminate();
    }

    public function actionUpload(): void
    {
        bdump($_FILES);

        if (empty($_FILES) || !isset($_FILES['file'])) {
            $this->terminate();
        }

        /*
            array
                'file' => array
                    'name' => 'coyote_knight_templar.jpg'
                    'full_path' => 'coyote_knight_templar.jpg'
                    'type' => 'image/jpeg'                          // MAX 255 CHARACTERS
                    'tmp_name' => 'C:\xampp82\tmp\php24B1.tmp'
                    'error' => 0
                    'size' => 202924
        */

        // $target_dir = 'uploads/';
        $target_dir = PROJECT_DIR . 'uploads/files/';
        $target_file = $target_dir . basename($_FILES['file']['name']);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is actual image or fake image
        if (isset($_POST['submit'])) {
            $check = getimagesize($_FILES['file']['tmp_name']);
            bdump($check, "TMP FILE IMAGE CHECK");

            if ($check === false) {
                $uploadOk = 0;
            }
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            bdump("File already exists.", "STATUS 1");
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES['file']['size'] > 5000000) {
            bdump("File is too large.", "STATUS 2");
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg','jpeg','png','gif','webp'])) {
            bdump("Only JPG, JPEG, PNG, GIF & WEBP files are allowed.", "STATUS 3");
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            bdump("File was not uploaded.", "STATUS 4");
        } else {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                bdump('File ' . basename($_FILES['file']['name']) . ' has been uploaded.', "STATUS 5");
            } else {
                bdump("There was an error uploading your file.", "STATUS 6");
            }
        }

        $this->terminate();
    }
}
