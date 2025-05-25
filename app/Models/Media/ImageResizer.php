<?php

declare(strict_types=1);

namespace App\Models\Media;

use GdImage;

class ImageResizer
{
    private const SUPPORTED_FORMATS = [
        'png' => IMAGETYPE_PNG,
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'gif' => IMAGETYPE_GIF,
        'webp' => IMAGETYPE_WEBP,
    ];
    private const DEFAULT_FORMAT = 'jpg';

    public function resizeToWidth(string $sourcePath, int $targetWidth, bool $allowUpscale = false): GdImage
    {
        $image = $this->createFromFile($sourcePath);
        $width = imagesx($image);
        $height = imagesy($image);

        if (!$allowUpscale && $width <= $targetWidth) {
            return $image;
        }

        $ratio = $targetWidth / $width;
        $newHeight = (int) round($height * $ratio);

        return $this->resize($image, $targetWidth, $newHeight);
    }

    public function resizeAndCrop(string $sourcePath, int $targetWidth, int $targetHeight, bool $allowUpscale = false): GdImage
    {
        $image = $this->createFromFile($sourcePath);
        $srcW = imagesx($image);
        $srcH = imagesy($image);

        if (!$allowUpscale && ($srcW < $targetWidth || $srcH < $targetHeight)) {
            return $image;
        }

        $srcRatio = $srcW / $srcH;
        $targetRatio = $targetWidth / $targetHeight;

        if ($srcRatio > $targetRatio) {
            $newH = $targetHeight;
            $newW = (int) round($targetHeight * $srcRatio);
        } else {
            $newW = $targetWidth;
            $newH = (int) round($targetWidth / $srcRatio);
        }

        $resized = $this->resize($image, $newW, $newH);

        $x = (int) (($newW - $targetWidth) / 2);
        $y = (int) (($newH - $targetHeight) / 2);

        $cropped = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopy($cropped, $resized, 0, 0, $x, $y, $targetWidth, $targetHeight);
        imagedestroy($resized);
        imagedestroy($image);

        return $cropped;
    }

    public function stretchResize(string $sourcePath, int $targetWidth, int $targetHeight): GdImage
    {
        $image = $this->createFromFile($sourcePath);
        return $this->resize($image, $targetWidth, $targetHeight);
    }

    public function saveToFile(GdImage $image, string $path, string $format = self::DEFAULT_FORMAT, int $quality = 95, bool $overwrite = false): bool
    {
        if (!$overwrite && is_file($path)) {
            return true;
        }

        if (!isset(self::SUPPORTED_FORMATS[$format])) {
            throw new \InvalidArgumentException("Unsupported format '$format'");
        }

        $type = self::SUPPORTED_FORMATS[$format];

        return match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, max(0, min($quality, 100))),
            IMAGETYPE_PNG  => imagepng($image, $path, max(0, min((int) round($quality / 10), 9))),
            IMAGETYPE_GIF  => imagegif($image, $path),
            IMAGETYPE_WEBP => imagewebp($image, $path, max(0, min($quality, 100))),
        };
    }

    // ##################################################
    // ###              INTERNAL METHODS              ###
    // ##################################################

    private function createFromFile(string $path): GdImage
    {
        $info = getimagesize($path);
        if ($info === false) {
            throw new \RuntimeException("Invalid image: '$path'");
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_GIF  => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => throw new \RuntimeException("Unsupported image type(ID {$info[2]}): '$path'"),
        };
    }

    private function resize(GdImage $image, int $newWidth, int $newHeight): GdImage
    {
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // zachování alfa kanálu (PNG, WebP)
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled(
            $resized,
            $image,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            imagesx($image),
            imagesy($image)
        );

        return $resized;
    }
}
