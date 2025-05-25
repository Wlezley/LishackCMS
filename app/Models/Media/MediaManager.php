<?php

declare(strict_types=1);

namespace App\Models\Media;

use Nette\Database\Explorer;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Nette\Utils\Strings;

class MediaManager
{
    public const TABLE_NAME = 'media';

    public const UPLOAD_DIR = 'uploads/media/';
    public const THUMB_DIR = 'uploads/media/thumbs/';

    public const UPLOAD_URL = HOME_URL . 'uploads/media/';
    public const THUMB_URL = HOME_URL . 'uploads/media/thumbs/';

    public const THUMB_WIDTH = 300;

    public function __construct(private Explorer $db)
    {}

    /**
     * Get all media files as array for JSON response
     *
     * @return array<array<string,string|int|null>>
     */
    public function getMediaList(): array
    {
        $rows = $this->db->table(self::TABLE_NAME)
            ->select('id, filename, title')
            ->order('created_at DESC');

        $list = [];

        foreach ($rows as $row) {
            $list[] = [
                'id'         => $row['id'],
                'filename'   => $row['filename'],
                'filepath'   => PROJECT_DIR . self::UPLOAD_DIR . $row['filename'],
                'thumb_path' => PROJECT_DIR . self::THUMB_DIR . $row['filename'],
                'url'        => self::UPLOAD_URL . $row['filename'],
                'thumb_url'  => self::THUMB_URL . $row['filename'],
                'title'      => $row['title'] ?? '',
            ];
        }

        return $list;
    }

    /**
     * Save uploaded image and create DB entry.
     *
     * @param FileUpload $file Uploaded file data object.
     * @param string|null $title Media title (default: null).
     * @param string|null $alt Media alternate text (default: null).
     * @return int inserted row ID
     */
    public function upload(FileUpload $file, ?string $title = null, ?string $alt = null): int
    {
        if (!$file->isOk() || !$file->isImage()) {
            throw new \InvalidArgumentException('Invalid image upload');
        }

        $ext = strtolower(pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION));
        $basename = Strings::webalize(pathinfo($file->getSanitizedName(), PATHINFO_FILENAME));
        $filename = $basename . '-' . uniqid() . '.' . $ext;

        // Save original image
        $filepath = PROJECT_DIR . self::UPLOAD_DIR . $filename;
        $file->move($filepath);

        // Create thumbnail
        // TODO: Use class ImageResizer
        $thumbPath = PROJECT_DIR . self::THUMB_DIR . $filename;
        $image = Image::fromFile($filepath);
        $image->resize(self::THUMB_WIDTH, null);
        $image->save($thumbPath);

        // Save metadata to DB
        $row = $this->db->table(self::TABLE_NAME)->insert([
            'filename' => $filename,
            'path'     => self::UPLOAD_DIR,
            'title'    => $title,
            'alt'      => $alt,
            'mime'     => $file->getContentType(),
            'size'     => $file->getSize(),
            'width'    => $image->getWidth(), // After resize?
            'height'   => $image->getHeight(),
        ]);

        return (int) $row['id'];
    }

    /**
     * Get single image info by ID.
     *
     * @param int $id Media ID.
     * @return array<string,mixed>|null
     */
    public function getById(int $id): ?array
    {
        $row = $this->db->table(self::TABLE_NAME)->get($id);

        if (!$row) {
            return null;
        }

        return [
            'id'         => $row['id'],
            'filename'   => $row['filename'],
            'filepath'   => PROJECT_DIR . self::UPLOAD_DIR . $row['filename'],
            'thumb_path' => PROJECT_DIR . self::THUMB_DIR . $row['filename'],
            'url'        => self::UPLOAD_URL . $row['filename'],
            'thumb_url'  => self::THUMB_URL . $row['filename'],
            'title'      => $row['title'] ?? '',
        ];
    }
}
