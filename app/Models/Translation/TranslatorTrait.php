<?php

declare(strict_types=1);

namespace App\Models\Translation;

use RuntimeException;

/**
 * Provides shorthand methods for retrieving and formatting translations.
 *
 * This trait requires that the consuming class defines a `$translator` property
 * with an instance of `Translator`. It provides:
 * - `t()` for retrieving translations.
 * - `tf()` for retrieving and formatting translations with parameters.
 *
 * Example usage:
 *
 * ```
 * php
 * class SomePresenter {
 *     use Translation;
 *
 *     private Translator $translator;
 *
 *     public function __construct(Translator $translator) {
 *         $this->translator = $translator;
 *     }
 *
 *     public function render(): void {
 *         echo $this->t('homepage.welcome');
 *         echo $this->tf('homepage.greeting', 'John');
 *     }
 * }
 * ```
 */
trait TranslatorTrait
{
    /**
     * Retrieves a translated text for a given key in a specified language.
     *
     * @param string $key The translation key.
     * @param string|null $lang Optional language code (defaults to current language).
     * @return string The translated text, or the key itself if not found.
     * @throws RuntimeException If Translator is not available.
     */
    public function t(string $key, ?string $lang = null): string
    {
        if (!isset($this->translator)) {
            throw new RuntimeException('Translator is not available in ' . static::class);
        }

        return $this->translator->translate($key, $lang);
    }

    /**
     * Translates a key and formats the translation with the given values.
     *
     * @param string $key The translation key.
     * @param mixed ...$values Values to be formatted into the translated string.
     * @return string The formatted translated text, or the key itself if translation is unavailable.
     * @throws RuntimeException If `Translator` is not available.
     */
    public function tf(string $key, mixed ...$values): string
    {
        if (!isset($this->translator)) {
            throw new RuntimeException('Translator is not available in ' . static::class);
        }

        return $this->translator->translateFormat($key, null, $values);
    }
}
