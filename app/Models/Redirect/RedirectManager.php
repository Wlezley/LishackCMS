<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use Nette\Database\Explorer;

class RedirectManager
{
    public const TABLE_NAME = 'redirect';

    public function __construct(
        private Explorer $db
    ) {}

    /**
     * Retrieves a redirect target URL (and HTTP code) for a given source URL.
     *
     * @param string $source Redirect trigger.
     * @param int|null &$code Output parameter for the HTTP redirect code.
     * @return string|null Redirect target URL, or null if not found.
     */
    public function get(string $source, ?int &$code = null): ?string
    {
        $redirect = $this->db->table(self::TABLE_NAME)
            ->select('target, code')
            ->where('source', $source)
            ->where('enabled', 1)
            ->fetch();

        if (!$redirect) {
            return null;
        }

        $code = (int) $redirect['code'];
        return $redirect['target'];
    }

    /**
     * Adds a new redirect entry.
     *
     * @param string $source Redirect source URL.
     * @param string $target Redirect target URL.
     * @param int $code HTTP redirect code (default: 302).
     * @param bool $enabled Whether the redirect is enabled (default: true).
     * @throws RedirectException If a redirect with the same source already exists.
     * @throws RedirectException If the source and target URLs are the same (to prevent redirect loops).
     */
    public function add(string $source, string $target, int $code = 302, bool $enabled = true): void
    {
        if ($source === $target) {
            throw new RedirectException("Redirect source and target cannot be the same (source:'$source')", 1);
        }

        $exists = $this->db->table(self::TABLE_NAME)
            ->where('source', $source)
            ->count('*');

        if ($exists) {
            throw new RedirectException("Duplicate redirect (source:'$source') found, entry cannot be added", 1);
        }

        $this->db->table(self::TABLE_NAME)->insert([
            'source' => $source,
            'target' => $target,
            'code' => $code,
            'enabled' => $enabled ? 1 : 0
        ]);
    }

    /**
     * Updates an existing redirect entry.
     *
     * @param string $source Redirect source URL.
     * @param string $target Redirect target URL.
     * @param int $code HTTP redirect code.
     * @param bool $enabled Whether the redirect is enabled.
     * @throws RedirectException If the redirect does not exist.
     * @throws RedirectException If the source and target URLs are the same (to prevent redirect loops).
     */
    public function update(string $source, string $target, int $code, bool $enabled): void
    {
        if ($source === $target) {
            throw new RedirectException("Redirect source and target cannot be the same (source:'$source')", 1);
        }

        $exists = $this->db->table(self::TABLE_NAME)
            ->where('source', $source)
            ->count('*');

        if (!$exists) {
            throw new RedirectException("Redirect (source:'$source') not found, entry cannot be modified", 1);
        }

        $this->db->table(self::TABLE_NAME)
            ->where('source', $source)
            ->update([
                'target' => $target,
                'code' => $code,
                'enabled' => $enabled ? 1 : 0
            ]);
    }

    /**
     * Deletes a redirect entry.
     *
     * @param string $source Redirect source URL to be deleted.
     */
    public function delete(string $source): void
    {
        $this->db->table(self::TABLE_NAME)
            ->where(['source' => $source])
            ->delete();
    }

    // LISTING METHODS

    // /** @return array<int|string,array<string,string|int|null>>|null */
    /**
     * Retrieves a list of redirects with optional search and pagination.
     *
     * @param int $limit Number of results to return (default: 50).
     * @param int $offset Offset for pagination (default: 0).
     * @param string|null $search Optional search query for source or target URLs.
     * @return array<int|string,array<string,string|int|null>>|null Array of redirects indexed by source, or null if empty.
     */
    public function getList(int $limit = 50, int $offset = 0, ?string $search = null): ?array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->limit($limit, $offset);

        if ($search !== null) {
            $query->where('source LIKE ? OR target LIKE ?', "%$search%", "%$search%");
        }

        $data = $query->fetchAll();

        return $data ? ArrayHelper::resultToArray($data, 'source') : null;
    }

    /**
     * Gets the total count of redirects, optionally filtered by a search query.
     *
     * @param string|null $search Optional search query for source or target URLs.
     * @return int Total count of matching redirects.
     */
    public function getCount(?string $search = null): int
    {
        $query = $this->db->table(self::TABLE_NAME);

        if ($search !== null) {
            $query->where('source LIKE ? OR target LIKE ?', "%$search%", "%$search%");
        }

        return $query->count('*');
    }
}
