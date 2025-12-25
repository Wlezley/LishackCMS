<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Models\Media\DummyImageGenerator;
use App\Models\Media\ImageResizer;
use App\Models\Media\MediaManager;
use Nette\Application\Responses\FileResponse;
use Nette\Http\IResponse;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

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

    /**
     * Handles the request to display or generate an image based on the provided parameters.
     *
     * If the image with the specified ID exists, it is processed and returned.
     * If resizing dimensions are provided (width, height), the image is resized or cropped accordingly.
     * If the image does not exist, a placeholder image is generated and returned.
     *
     * @param int $id The ID of the image to be displayed. Defaults to 0.
     * @param int<1, max>|null $width Optional width to resize or crop the image. If null, no width resizing is applied.
     * @param int<1, max>|null $height Optional height to resize or crop the image. If null and width are provided, the image is resized proportionately to the width.
     * @return void
     */
    public function actionShow(int $id = 0, ?int $width = null, ?int $height = null): void
    {
        $imageMetadata = $this->mediaManager->getById($id);

        if ($imageMetadata) {
            $fileName = $imageMetadata['filename'];
            $absoluteFilePath = $imageMetadata['filepath'];
            $contentType = mime_content_type($absoluteFilePath);

            try {
                Assert::string($contentType);
            } catch (InvalidArgumentException $e) {
                $this->error('Invalid content type for media file', IResponse::S400_BadRequest);
            }

            if ($width !== null || $height !== null) {
                if ($height === null) {
                    $thumbPath = PROJECT_DIR . MediaManager::THUMB_DIR . "$id-$width.png";
                    $image = $this->imageResizer->resizeToWidth($absoluteFilePath, $width);
                } else {
                    Assert::integer($width);
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
