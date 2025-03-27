<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Provides a shortcut method for accessing configuration values.
 *
 * This trait requires that the consuming class defines a `$configManager` property
 * with an instance of `ConfigManager`. It provides:
 * - `c()` shorthand method to retrieve configuration values.
 *
 * Example usage:
 *
 * ```php
 * class SomeService {
 *     use Config;
 *
 *     private ConfigManager $configManager;
 *
 *     public function __construct(ConfigManager $configManager) {
 *         $this->configManager = $configManager;
 *     }
 *
 *     public function doSomething(): void {
 *         $value = $this->c('some_key');
 *         // ...
 *     }
 * }
 * ```
 */
trait Config
{
    /**
     * Retrieves a configuration value for a given key.
     *
     * @param string $key The configuration key.
     * @return string|null The configuration value, or null if not found.
     * @throws \RuntimeException If ConfigManager is not available.
     */
    public function c(string $key): ?string
    {
        if (!isset($this->configManager)) {
            throw new \RuntimeException('ConfigManager is not available in ' . static::class);
        }

        return $this->configManager->get($key);
    }
}
