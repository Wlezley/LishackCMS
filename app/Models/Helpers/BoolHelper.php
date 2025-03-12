<?php

declare(strict_types=1);

namespace App\Models\Helpers;


class BoolHelper
{
    /**
     * Check BOOL condition
     *
     * @param  mixed $variable  Can be anything (string, bol, integer, etc.)
     * @return bool|null        Returns TRUE  for 1, "1", "true", "on", "yes" and "enabled"
     *                          Returns FALSE for 0, "0", "false", "off", "no" and "disabled"
     *                          Returns NULL otherwise.
     */
    public static function is_enabled(mixed $variable): bool|null
    {
        if (in_array($variable, [1, '1', 'on', 'yes', 'enabled', true], true)) {
            return true;
        } elseif (in_array($variable, [0, '0', 'off', 'no', 'disabled', false], true)) {
            return false;
        } else {
            return null;
        }
    }

    /**
     * Check BOOL type
     *
     * @param  mixed $variable  Can be anything (string, bol, integer, etc.)
     * @return bool|null        Returns TRUE  for 1, "1", "true", "on", "yes", "enabled",
     *                                            0, "0", "false", "off", "no" and "disabled"
     *                          Returns FALSE otherwise.
     */
    public static function is_bool(mixed $variable): bool|null
    {
        return self::is_enabled($variable) !== null;
    }
}
