<?php

declare(strict_types=1);

namespace App\Models\Media;

use GdImage;

class DummyImageGenerator
{
    private const FONT_DIR = PROJECT_DIR . 'www/assets/fonts/';
    private const FONT_FILE = 'Audiowide-Regular.ttf';
    // private const FONT_LICENSE = self::FONT_DIR . 'Audiowide-LICENSE.md';

    private const CACHE_DIR = PROJECT_DIR . 'uploads/media/cache/';
    private const CACHE_PREFIX = 'no-image';

    private const SUPPORTED_FORMATS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    private const DEFAULT_FORMAT = 'png';

    private const DEFAULT_BG_COLOR = '#ffc107';
    private const DEFAULT_TEXT_COLOR = '#000000';

    private int $width;
    private ?int $height = null;
    private ?string $caption = null;
    private string $bgColor = self::DEFAULT_BG_COLOR;
    private string $textColor = self::DEFAULT_TEXT_COLOR;
    private string $format = self::DEFAULT_FORMAT;
    private ?string $binaryData = null;

    /**
     * Sets the dimensions of the image. If height is not provided, it defaults to a square image.
     *
     * @param int $width Width of the image in pixels.
     * @param int|null $height Height of the image in pixels. If null, same as width.
     * @return self
     */
    public function setSize(int $width, ?int $height = null): self
    {
        $this->width = $width;
        $this->height = $height ?? $width;
        return $this;
    }

    /**
     * Sets a custom caption to be displayed on the image.
     *
     * @param string $caption Text caption to display on the image.
     * @return self
     */
    public function setCaption(string $caption): self
    {
        $this->caption = $caption;
        return $this;
    }

    /**
     * Sets the background color of the image in hexadecimal format.
     *
     * @param string $bgColor Background color as hex code (e.g., "#cccccc" or "#ccc").
     * @return self
     */
    public function setBgColor(string $bgColor = self::DEFAULT_BG_COLOR): self
    {
        $this->bgColor = $bgColor;
        return $this;
    }

    /**
     * Sets the text color in hexadecimal format.
     *
     * @param string $textColor Text color as hex code (e.g., "#000000" or "#000").
     * @return self
     */
    public function setTextColor(string $textColor = self::DEFAULT_TEXT_COLOR): self
    {
        $this->textColor = $textColor;
        return $this;
    }

    /**
     * Sets the output image format.
     *
     * @param string $format Output format. Supported formats: png, jpg, jpeg, gif, webp.
     * @return self
     *
     * @throws \InvalidArgumentException If the format is not supported.
     */
    public function setFormat(string $format = self::DEFAULT_FORMAT): self
    {
        $format = strtolower($format);

        if (!in_array($format, self::SUPPORTED_FORMATS, true)) {
            throw new \InvalidArgumentException("Unsupported format '$format'");
        }

        $this->format = $format;
        return $this;
    }

    /**
     * Generates the image. Returns cached data if available, otherwise renders and caches the image.
     *
     * @return self
     * @throws \RuntimeException If image generating fails.
     */
    public function generate(): self
    {
        if (!isset($this->width, $this->height)) {
            throw new \LogicException('Image dimensions must be set before generating.');
        }

        $cachePath = $this->getCacheFilePath();
        if (is_file($cachePath)) {
            $cached = file_get_contents($cachePath);
            if ($cached !== false) {
                $this->binaryData = $cached;
                return $this;
            } else {
                throw new \RuntimeException("Unable to read cache file: $cachePath");
            }
        }

        $image = $this->createImage();
        $this->drawText($image);

        try {
            $output = $this->renderImageToString($image);
        } catch (\Throwable $e) {
            imagedestroy($image);
            throw new \RuntimeException('Failed to render image: ' . $e->getMessage(), 0, $e);
        }

        $this->saveToCache($cachePath, $output);

        $this->binaryData = $output;
        return $this;
    }

    /**
     * Builds file name of cache file based on parameters and format.
     *
     * @return string Name of the cache file.
     */
    public function getCacheFileName(): string
    {
        $uniquePhrase = $this->getSafeCaption() . '|' . $this->bgColor . '|' . $this->textColor;
        $hash = substr(md5($uniquePhrase), 0, 8);
        return sprintf('%s-%dx%d-%s.%s', self::CACHE_PREFIX, $this->width, $this->height, $hash, $this->format);
    }

    /**
     * Builds the full path to the cache file based on parameters and format.
     *
     * @return string Full path to the cache file.
     */
    public function getCacheFilePath(): string
    {
        return self::CACHE_DIR . $this->getCacheFileName();
    }

    /**
     * Gets image format MIME.
     *
     * @return string
     */
    public function getFormatMime(): string
    {
        return "image/{$this->format}";
    }

    /**
     * Gets binary data of generated (or cached) image.
     *
     * @return string|null
     */
    public function getBinaryData(): ?string
    {
        return $this->binaryData;
    }

    /**
     * Reset class to defaults
     */
    public function reset(): void
    {
        $this->width = 1024;
        $this->height = null;
        $this->caption = null;
        $this->bgColor = self::DEFAULT_BG_COLOR;
        $this->textColor = self::DEFAULT_TEXT_COLOR;
        $this->format = self::DEFAULT_FORMAT;
    }

    // ##################################################
    // ###              INTERNAL METHODS              ###
    // ##################################################

    /**
     * Saves the rendered image data to a cache file.
     *
     * @param string $path Full file path for cache.
     * @param string $data Image data as binary string.
     */
    private function saveToCache(string $path, string $data): void
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            throw new \RuntimeException("Cache directory does not exist: $directory");
        }

        if (!is_writable($directory)) {
            throw new \RuntimeException("Cache directory is not writable: $directory");
        }

        if (file_put_contents($path, $data) === false) {
            throw new \RuntimeException("Failed to write image cache file: $path");
        }
    }

    /**
     * Returns the caption to use on the image. If no caption is set, returns dimensions string (width X height).
     *
     * @return string Final caption string.
     */
    private function getSafeCaption(): string
    {
        if (!$this->caption) {
            return sprintf('%d×%d', $this->width, $this->height);
        }

        return $this->caption;
    }

    /**
     * Creates a blank true color image and fills it with the background color.
     *
     * @return GdImage The initialized GD image resource.
     */
    private function createImage(): GdImage
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        $bgRGB = $this->hexToRgb($this->bgColor);
        $bg = imagecolorallocate($image, ...$bgRGB);
        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $bg);

        return $image;
    }

    /**
     * Draws the caption text centered on the image using a TTF font.
     *
     * @param GdImage $image GD image resource.
     *
     * @throws \RuntimeException If the font file is not found or text bounding box cannot be calculated.
     */
    private function drawText(GdImage $image): void
    {
        $fontPath = self::FONT_DIR . self::FONT_FILE;
        if (!is_file($fontPath)) {
            throw new \RuntimeException("Font file not found: $fontPath");
        }

        $fontSize = (int) max(10, min($this->width, $this->height) / 10);

        $textRGB = $this->hexToRgb($this->textColor);
        $tc = imagecolorallocate($image, ...$textRGB);

        $caption = $this->getSafeCaption();

        $bbox = imagettfbbox($fontSize, 0, $fontPath, $caption);
        if ($bbox === false) {
            throw new \RuntimeException('Failed to calculate text bounding box.');
        }

        $textWidth = abs($bbox[4] - $bbox[0]);
        $textHeight = abs($bbox[5] - $bbox[1]);

        $x = (int) (($this->width - $textWidth) / 2);
        $y = (int) (($this->height + $textHeight) / 2);

        imagettftext($image, $fontSize, 0, $x, $y, $tc, $fontPath, $caption);
    }

    /**
     * Renders the GD image to a binary string in the specified format.
     *
     * @param GdImage $image GD image resource.
     * @return string Image data as binary string.
     *
     * @throws \RuntimeException If rendering fails.
     */
    private function renderImageToString(GdImage $image): string
    {
        ob_start();
        match ($this->format) {
            'png'  => imagepng($image),
            'jpg', 'jpeg' => imagejpeg($image, null, 90),
            'gif'  => imagegif($image),
            'webp' => imagewebp($image),
            default => throw new \RuntimeException("Unsupported image format: $this->format"),
        };
        imagedestroy($image);
        $data = ob_get_clean();

        if ($data === false) {
            throw new \RuntimeException("Failed to render image.");
        }

        return $data;
    }

    /**
     * Converts a hexadecimal color string to an RGB array.
     *
     * @param string $hex Hexadecimal color code (e.g., "#ffffff" or "fff").
     * @return array{0: int, 1: int, 2: int} RGB color as an indexed array. (0: red, 1: green, 2: blue)
     *
     * @throws \InvalidArgumentException If the input is not a valid hex color.
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            throw new \InvalidArgumentException('Invalid hex color value.');
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
