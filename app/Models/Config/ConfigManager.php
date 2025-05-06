<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class ConfigManager
{
    public const TABLE_NAME = 'cms_config';

    /** @var array<string,array<string,string>> Configuration data indexed by setting key. */
    private array $configuration = [];

    public function __construct(
        private Explorer $db
    ) {}

    /**
     * Loads configuration data from the database if not already loaded.
     */
    private function load(): void
    {
        if (empty($this->configuration)) {
            $this->configuration = ArrayHelper::resultToArray(
                $this->db->table(self::TABLE_NAME)->fetchAll(),
                'key'
            );
        }
    }

    /**
     * Forces reloading of configuration data from the database.
     */
    public function reload(): void
    {
        $this->invalidate();
        $this->load();
    }

    /**
     * Clears the cached configuration data.
     */
    public function invalidate(): void
    {
        $this->configuration = [];
    }

    /**
     * Retrieves a configuration value by key.
     *
     * @param string $key Configuration key.
     * @return string|null The configuration value, or null if not found.
     */
    public function get(string $key): ?string
    {
        $this->load();
        return $this->configuration[$key]['value'] ?? null;
    }

    /**
     * Returns an associative array of all configuration values.
     *
     * @return array<string,string> Associative array of configuration keys and their values.
     */
    public function getConfigValues(): array
    {
        $this->load();

        $values = [];
        foreach ($this->configuration as $key => $item) {
            $values[$key] = $item['value'];
        }

        return $values;
    }

    /**
     * Retrieves configuration values belonging to a specific category.
     *
     * @param string $category The category name.
     * @return array<string,string> Associative array of category values (empty if none found).
     */
    public function getCategoryValues(string $category): array
    {
        $this->load();

        $categoryValues = [];
        foreach ($this->configuration as $key => $item) {
            if ($item['category'] == $category) {
                $categoryValues[$key] = $item['value'];
            }
        }

        return $categoryValues;
    }

    /**
     * Retrieves a list of configuration categories with the number of items in each.
     *
     * @return array<string,int> Associative array where keys are category names and values are the number of items in each category.
     */
    public function getCategories(): array
    {
        $this->load();

        $categories = [];
        foreach ($this->configuration as $item) {
            $category = $item['category'];
            if (isset($categories[$category])) {
                $categories[$category] += 1;
            } else {
                $categories[$category] =  1;
            }
        }

        return $categories;
    }

    /**
     * Adds or updates a configuration entry.
     *
     * If the key already exists, it updates the value and category.
     * Otherwise, it inserts a new entry.
     *
     * @param string $key Configuration key.
     * @param string $category Category name.
     * @param string $value Configuration value.
     */
    public function set(string $key, string $category, string $value): void
    {
        $this->load();

        if (isset($this->configuration[$key])) {
            $this->db->table(self::TABLE_NAME)->where([
                'key' => $key
            ])->update([
                'category' => $category,
                'value' => $value
            ]);
        } else {
            $this->db->table(self::TABLE_NAME)->insert([
                'key' => $key,
                'category' => $category,
                'value' => $value
            ]);
        }

        $this->invalidate();
    }

    /**
     * Adds a new configuration entry.
     *
     * Throws an exception if the key already exists.
     *
     * @param string $key Configuration key.
     * @param string $category Category name.
     * @param string $value Configuration value.
     * @throws ConfigException If the key already exists.
     */
    public function add(string $key, string $category, string $value): void
    {
        $this->load();

        if (isset($this->configuration[$key])) {
            throw new ConfigException("Duplicate key '$key' found, configuration entry cannot be inserted", 1);
        }

        $item = ['key' => $key, 'category' => $category, 'value' => $value];

        $this->db->table(self::TABLE_NAME)
            ->insert($item);

        $this->configuration[$key] = $item;
    }

    /**
     * Updates an existing configuration entry.
     *
     * If the key does not exist, this method does nothing.
     *
     * @param string $key Configuration key.
     * @param string $category Category name.
     * @param string $value New configuration value.
     * @throws ConfigException If the key not found.
     */
    public function update(string $key, string $category, string $value): void
    {
        $this->load();

        if (!isset($this->configuration[$key])) {
            throw new ConfigException("Key '$key' not found, configuration entry cannot be updated", 1);
        }

        $item = ['key' => $key, 'category' => $category, 'value' => $value];

        $this->db->table(self::TABLE_NAME)->where([
            'key' => $key
        ])->update([
            'category' => $category,
            'value' => $value
        ]);

        $this->configuration[$key] = $item;
    }

    /**
     * Updates configuration value.
     *
     * If the key does not exist, this method does nothing.
     *
     * @param string $key Configuration key.
     * @param string $value New configuration value.
     * @throws ConfigException If the key not found.
     */
    public function updateValue(string $key, string $value): void
    {
        $this->load();

        if (!isset($this->configuration[$key])) {
            throw new ConfigException("Key '$key' not found, configuration entry cannot be updated", 1);
        }

        $this->db->table(self::TABLE_NAME)->where([
            'key' => $key
        ])->update([
            'value' => $value
        ]);

        $this->configuration[$key]['value'] = $value;
    }

    /**
     * Renames a configuration key.
     *
     * Throws an exception if the old key does not exist or the new key already exists.
     *
     * @param string $oldKey The existing key.
     * @param string $newKey The new key.
     * @throws ConfigException If the old key is not found or the new key already exists.
     */
    public function changeKey(string $oldKey, string $newKey): void
    {
        $this->load();

        if (!isset($this->configuration[$oldKey])) {
            throw new ConfigException("Key '$oldKey' not found, key cannot be changed", 1);
        }

        if (isset($this->configuration[$newKey])) {
            throw new ConfigException("Duplicate key '$newKey' found, key cannot be changed", 1);
        }

        $this->db->table(self::TABLE_NAME)->where([
            'key' => $oldKey
        ])->update([
            'key' => $newKey
        ]);

        $this->invalidate();
    }

    /**
     * Deletes a configuration entry.
     *
     * @param string $key Configuration key to be removed.
     */
    public function delete(string $key): void
    {
        // $this->load();
        // if (!isset($this->configuration[$key])) {
        //     throw new ConfigException("Key '$key' not found, configuration entry cannot be deleted", 1);
        // }

        $this->db->table(self::TABLE_NAME)
            ->where('key', $key)
            ->delete();

        $this->invalidate();
    }

    // LISTING METHODS

    /**
     * Retrieves a list of configuration entries with optional filtering.
     *
     * @param int $limit Number of entries to retrieve.
     * @param int $offset Offset for pagination.
     * @param string|null $category Filter by category (optional).
     * @param string|null $search Search term for key or value (optional).
     * @return array<ActiveRow> List of configuration entries.
     */
    public function getList(int $limit = 50, int $offset = 0, ?string $category = null, ?string $search = null): array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->limit($limit, $offset);

        if ($category !== null) {
            $query->where('category', $category);
        }

        if ($search !== null) {
            $query->whereOr([
                'key LIKE ?' => "%$search%",
                'value LIKE ?' => "%$search%"
            ]);
        }

        // DEBUG: Array test @return array<T|mixed>
        // return ArrayHelper::resultToArray($query->fetchAll(), null);

        return $query->fetchAll();
    }

    /**
     * Returns the total count of configuration entries with optional filtering.
     *
     * @param string|null $category Filter by category (optional).
     * @param string|null $search Search term for key or value (optional).
     * @return int Number of matching entries.
     */
    public function getCount(?string $category = null, ?string $search = null): int
    {
        $query = $this->db->table(self::TABLE_NAME);

        if ($category !== null) {
            $query->where('category', $category);
        }

        if ($search !== null) {
            $query->whereOr([
                'key LIKE ?' => "%$search%",
                'value LIKE ?' => "%$search%"
            ]);
        }

        return $query->count('*');
    }

    // CONFIG EDITOR METHODS

    /**
     * Saves multiple configuration values in batch.
     *
     * Updates existing configuration entries if their value or category has changed.
     * Otherwise, inserts new configuration entries.
     *
     * @param array<string, array{category: string, value: string}> $configuration Associative array of configuration items.
     */
    public function saveConfig(array $configuration): void
    {
        $this->load();
        $oldConfig = $this->configuration;

        foreach ($configuration as $key => $item) {
            if (isset($oldConfig[$key])) {
                if ($oldConfig[$key]['category'] != $item['category'] || $oldConfig[$key]['value'] != $item['value']) {
                    $this->update($key, $item['category'], $item['value']);
                }
                if (empty($item['category']) && empty($item['value'])) {
                    $this->delete($key);
                }
            } else if ($item['category'] || $item['value']) {
                $this->add($key, $item['category'], $item['value']);
            }
        }

        $this->invalidate();
    }

    /**
     * Returns configuration data.
     *
     * @return array<string,array<string,string>> Associative array of configuration data indexed by setting key.
     */
    public function getConfigData(): array
    {
        $this->load();
        $sortedData = $this->configuration;
        ksort($sortedData);

        return $sortedData;
    }

    // VALUE UPDATE FORMS

    /**
     * Updates multiple configuration values in batch.
     *
     * This function updates only existing configuration entries if their value has changed.
     * It does not create new entries or delete any keys.
     *
     * @param array<string,string> $configuration List of configuration items.
     */
    public function saveConfigValues(array $configuration): void
    {
        $this->load();

        foreach ($configuration as $key => $value) {
            if (isset($this->configuration[$key]) && $this->configuration[$key]['value'] != $value) {
                $this->updateValue($key, (string)$value);
            }
        }

        $this->invalidate();
    }
}
