<?php

declare(strict_types=1);

namespace App\Models\Config;

use RuntimeException;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

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
trait ConfigTrait
{
    /**
     * Retrieves a configuration value for a given key
     *
     * @param string $key The configuration key
     * @return string|null The configuration value or null if not found
     * @throws RuntimeException If ConfigManager is not available
     * @throws InvalidArgumentException If the configuration key is empty
     */
    public function c(string $key): ?string
    {
        try {
            Assert::isInitialized($this, 'configManager', 'ConfigManager is not available in ' . static::class);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException('ConfigManager is not available in ' . static::class, 0, $e);
        }

        return $this->configManager->get($key);
    }
}
