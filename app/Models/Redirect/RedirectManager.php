<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use Nette\Database\Explorer;

class RedirectManager
{
    public const TABLE_NAME = 'redirect';

    public const REDIRECT_HTTP_CODES = [
        // 300 => '300 Multiple Choices', // Choices are listed in an HTML page in the body. Machine-readable choices are encouraged to be sent as Link headers with rel=alternate.
        301 => '301 Moved Permanently', // Reorganization of a website.
        302 => '302 Found', // The Web page is temporarily unavailable for unforeseen reasons.
        // 303 => '303 See Other', // Used to redirect after a PUT or a POST, so that refreshing the result page doesn't re-trigger the operation.
        // 304 => '304 Not Modified', // Sent for revalidated conditional requests. Indicates that the cached response is still fresh and can be used.
        // 307 => '307 Temporary Redirect', // The Web page is temporarily unavailable for unforeseen reasons. Better than 302 when non-GET operations are available on the site.
        // 308 => '308 Permanent Redirect', // Reorganization of a website, with non-GET links/operations.
        // 403 => '403 Forbidden', // Server understood the request but refused to process it.
        // 404 => '404 Not Found', // Links that lead to a 404 page are often called broken or dead links and can be subject to link rot.
    ];

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
        $source = $this->prepare($source);

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
     * Retrieves a single redirect entry as an associative array.
     *
     * @param int|string $id Redirect ID.
     * @return array<string, mixed>|null Associative array of redirect data if found, otherwise null.
     */
    public function getRow(int|string $id): ?array
    {
        $redirect = $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->fetch();

        if (!$redirect) {
            return null;
        }

        return $redirect->toArray();
    }

    /**
     * Adds a new redirect entry.
     *
     * @param string $source Redirect source URL.
     * @param string $target Redirect target URL.
     * @param int $code HTTP redirect code (default: 302).
     * @param bool $enabled Whether the redirect is enabled (default: true).
     * @return int|null ID of inserted row, or null if none.
     * @throws RedirectException If a redirect with the same source already exists.
     * @throws RedirectException If the source and target URLs are the same (to prevent redirect loops).
     * @throws RedirectException If the HTTP redirect code is invalid.
     */
    public function add(string $source, string $target, int $code = 302, bool $enabled = true): ?int
    {
        $source = $this->prepare($source);
        $target = $this->prepare($target);

        if ($source === $target) {
            throw new RedirectException("Redirect source and target cannot be the same", 1);
        }

        if (!$this->checkHttpCode($code)) {
            throw new RedirectException("HTTP code '$code' is invalid or not supported", 1);
        }

        $exists = $this->db->table(self::TABLE_NAME)
            ->where('source', $source)
            ->count('*');

        if ($exists) {
            throw new RedirectException("Duplicate redirect for source '$source' found, entry cannot be added", 1);
        }

        $result = $this->db->table(self::TABLE_NAME)->insert([
            'source' => $source,
            'target' => $target,
            'code' => $code,
            'enabled' => $enabled ? 1 : 0
        ]);

        if (is_numeric($result['id'])) {
            return (int)$result['id'];
        }

        return null;
    }

    /**
     * Updates an existing redirect entry.
     *
     * @param int|string $id Redirect ID.
     * @param string $source Redirect source URL.
     * @param string $target Redirect target URL.
     * @param int $code HTTP redirect code.
     * @param bool $enabled Whether the redirect is enabled.
     * @return bool True if a row was updated, false if no matching row was found.
     * @throws RedirectException If the redirect does not exist.
     * @throws RedirectException If the source and target URLs are the same (to prevent redirect loops).
     * @throws RedirectException If the source is empty.
     * @throws RedirectException If the target is empty.
     * @throws RedirectException If the HTTP redirect code is invalid.
     * @throws RedirectException If the new source URL is not unique.
     */
    public function update(int|string $id, string $source, string $target, int $code, bool $enabled): bool
    {
        $source = $this->prepare($source);
        $target = $this->prepare($target);

        if ($source === $target) {
            throw new RedirectException("Redirect source and target cannot be the same", 1);
        }

        if ($source === null) {
            throw new RedirectException("Redirect source cannot be empty", 1);
        }

        if ($target === null) {
            throw new RedirectException("Redirect target cannot be empty", 1);
        }

        if (!$this->checkHttpCode($code)) {
            throw new RedirectException("HTTP code '$code' is invalid or not supported", 1);
        }

        $query = $this->db->table(self::TABLE_NAME)
            ->where('id', $id);

        $originalRow = $query->fetch();

        if (!$originalRow) {
            throw new RedirectException("Redirect ID '$id' not found, entry cannot be modified", 1);
        }

        if ($originalRow['source'] != $source && $this->get($source) !== null) {
            throw new RedirectException("Redirect source '$source' is not unique", 1);
        }

        $affectedRows = $query->update([
            'source' => $source,
            'target' => $target,
            'code' => $code,
            'enabled' => $enabled ? 1 : 0
        ]);

        return ($affectedRows == 1);
    }

    /**
     * Deletes a redirect entry.
     *
     * @param int|string $id Redirect ID to be deleted.
     * @return bool True if a row was deleted, false if no matching row was found.
     */
    public function delete(int|string $id): bool
    {
        $affectedRows = $this->db->table(self::TABLE_NAME)
            ->where(['id' => $id])
            ->delete();

        return ($affectedRows == 1);
    }

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
            ->limit($limit, $offset)
            ->order('id ASC');

        if ($search !== null) {
            $query->where('source LIKE ? OR target LIKE ?', "%$search%", "%$search%");
        }

        $data = $query->fetchAll();

        return $data ? ArrayHelper::resultToArray($data) : null;
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

    /**
     * Checks if the given HTTP status code is a valid redirect or error code.
     *
     * @param int $httpCode HTTP status code to check.
     * @return bool True if the code is in the predefined list, false otherwise.
     */
    public function checkHttpCode(int $httpCode): bool
    {
        return array_key_exists($httpCode, self::REDIRECT_HTTP_CODES);
    }

    /**
     * Prepares a URL by trimming whitespace, normalizing slashes, and ensuring consistency.
     *
     * @param string $url URL to be prepared.
     * @return string|null Prepared URL or null if empty.
     */
    private function prepare(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? null;
        $host = $parsed['host'] ?? null;
        $path = $parsed['path'] ?? '';
        $path = preg_replace('~//+~', '/', $path);

        return ($scheme ? "$scheme://" : '') . ($host ? $host : '') . '/' . ltrim($path, '/');
    }
}
