<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Models\Media\DummyImageGenerator;
use App\Models\Media\ImageResizer;
use App\Models\Media\MediaManager;
use Nette\Application\Responses\FileResponse;

final class MediaPresenter extends BasePresenter
{
    /** @var DummyImageGenerator @inject */
    public DummyImageGenerator $dummyImage;

    /** @var ImageResizer @inject */
    public ImageResizer $imageResizer;

    /** @var MediaManager @inject */
    public MediaManager $mediaManager;

    public function actionDefault(string $fileName = ''): void
    {
        $absoluteFilePath = PROJECT_DIR . MediaManager::UPLOAD_DIR . $fileName;
        $contentType = 'image/png';

        $response = new FileResponse($absoluteFilePath, $fileName, $contentType, false);
        $this->sendResponse($response);
    }

    public function actionShow(int $id = 0, ?int $width = null, ?int $height = null): void
    {
        $imageMetadata = $this->mediaManager->getById($id);

        if ($imageMetadata) {
            $fileName = $imageMetadata['filename'];
            $absoluteFilePath = $imageMetadata['filepath'];
            $contentType = mime_content_type($absoluteFilePath);

            if ($width || $height) {
                if (!$height) {
                    $thumbPath = PROJECT_DIR . MediaManager::THUMB_DIR . "$id-$width.png";
                    $image = $this->imageResizer->resizeToWidth($absoluteFilePath, $width);
                } else {
                    $thumbPath = PROJECT_DIR . MediaManager::THUMB_DIR . "$id-$width-$height.png";
                    $image = $this->imageResizer->resizeAndCrop($absoluteFilePath, $width, $height);
                }

                $this->imageResizer->saveToFile($image, $thumbPath);
                $absoluteFilePath = $thumbPath;
            }

            $response = new FileResponse($absoluteFilePath, $fileName, $contentType, false);
            $this->sendResponse($response);
        } else {
            $this->dummyImage->reset();

            if (isset($width, $height)) {
                $this->dummyImage->setSize($width, $height);
            } elseif ($width) {
                $this->dummyImage->setSize($width);
            } else {
                $this->dummyImage->setSize(1024, 1024);
                $this->dummyImage->setCaption("Image ID: $id");
            }

            $this->dummyImage
                ->setFormat('webp')
                ->generate();

            $fileName = $this->dummyImage->getCacheFileName();
            $absoluteFilePath = $this->dummyImage->getCacheFilePath();

            $response = new FileResponse($absoluteFilePath, $fileName, $this->dummyImage->getFormatMime(), false);
            $this->sendResponse($response);
        }
    }

    public function actionJson(): void
    {
        $this->sendJson($this->mediaManager->getMediaList());
    }
}
