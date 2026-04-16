<?php

declare(strict_types=1);

namespace App\Models\Media;

use GdImage;
use Webmozart\Assert\Assert;

class ImageResizer
{
    /** @var array<string, int> */
    private const array SUPPORTED_FORMATS = [
        'png' => IMAGETYPE_PNG,
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'gif' => IMAGETYPE_GIF,
        'webp' => IMAGETYPE_WEBP,
    ];
    private const string DEFAULT_FORMAT = 'jpg';

    /**
     * Resizes an image to fit the given width, maintaining an aspect ratio.
     *
     * @param string $sourcePath The path to the source image file.
     * @param int<1, max> $targetWidth The target width of the resized image.
     * @param bool $allowUpscale Whether to allow upscaling of the source image if it is smaller than the target width.
     * @return GdImage The resulting resized image resource.
     */
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
        Assert::range($newHeight, 1, PHP_INT_MAX);

        return $this->resize($image, $targetWidth, $newHeight);
    }

    /**
     * Resizes an image to fit the given dimensions, maintaining an aspect ratio, and crops it to the target size.
     *
     * @param string $sourcePath The path to the source image file.
     * @param int<1, max> $targetWidth The target width of the cropped image.
     * @param int<1, max> $targetHeight The target height of the cropped image.
     * @param bool $allowUpscale Whether to allow upscaling of the source image if it is smaller than the target dimensions.
     * @return GdImage The resulting cropped and resized image resource.
     */
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

        Assert::range($newW, 1, PHP_INT_MAX);
        Assert::range($newH, 1, PHP_INT_MAX);

        $resized = $this->resize($image, $newW, $newH);

        $x = (int) (($newW - $targetWidth) / 2);
        $y = (int) (($newH - $targetHeight) / 2);

        Assert::range($targetWidth, 1, PHP_INT_MAX);
        Assert::range($targetHeight, 1, PHP_INT_MAX);

        $cropped = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopy(
            dst_image: $cropped,
            src_image: $resized,
            dst_x: 0,
            dst_y: 0,
            src_x: $x,
            src_y: $y,
            src_width: $targetWidth,
            src_height: $targetHeight
        );

        // Cleanup
        imagedestroy($resized);
        imagedestroy($image);

        return $cropped;
    }

    /**
     * Resizes an image to fit the given dimensions, stretching if necessary.
     *
     * @param string $sourcePath The path to the source image file.
     * @param int<1, max> $targetWidth The target width of the resized image.
     * @param int<1, max> $targetHeight The target height of the resized image.
     * @return GdImage The resulting resized image resource.
     */
    public function stretchResize(string $sourcePath, int $targetWidth, int $targetHeight): GdImage
    {
        $image = $this->createFromFile($sourcePath);
        return $this->resize($image, $targetWidth, $targetHeight);
    }

    public function saveToFile(
        GdImage $image,
        string $path,
        string $format = self::DEFAULT_FORMAT,
        int $quality = 95,
        bool $overwrite = false
    ): bool {
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

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_GIF  => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => throw new \RuntimeException("Unsupported image type(ID {$info[2]}): '$path'"),
        };

        Assert::isInstanceOf($image, GdImage::class);

        return $image;
    }

    /**
     * Resizes the given image to the specified dimensions.
     *
     * @param GdImage $image The original image resource.
     * @param int<1, max> $newWidth The desired width of the resized image.
     * @param int<1, max> $newHeight The desired height of the resized image.
     * @return GdImage The resized image resource.
     * @return GdImage The resized image resource.
     */
    private function resize(GdImage $image, int $newWidth, int $newHeight): GdImage
    {
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // preserve alpha channel (PNG, WebP)
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled(
            dst_image: $resized,
            src_image: $image,
            dst_x: 0,
            dst_y: 0,
            src_x: 0,
            src_y: 0,
            dst_width: $newWidth,
            dst_height: $newHeight,
            src_width: imagesx($image),
            src_height: imagesy($image)
        );

        return $resized;
    }
}
