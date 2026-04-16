<?php

declare(strict_types=1);

namespace App\Models\Helpers;

class BoolHelper
{
    /**
     * Check BOOL condition
     *
     * @param  mixed $variable  Can be anything (string, bol, integer, etc.)
     * @return bool|null        Returns TRUE for 1, "1", "true", "on", "yes" and "enabled"
     *                          Returns FALSE for 0, "0", "false", "off", "no" and "disabled"
     *                          Returns NULL otherwise.
     */
    public static function isEnabled(mixed $variable): ?bool
    {
        if (in_array($variable, [1, '1', 'on', 'yes', 'enabled', 'true', true], true)) {
            return true;
        } elseif (in_array($variable, [0, '0', 'off', 'no', 'disabled', 'false', false], true)) {
            return false;
        } else {
            return null;
        }
    }

    /**
     * Check BOOL type
     *
     * @param  mixed $variable  Can be anything (string, bol, integer, etc.)
     * @return bool             Returns TRUE for 1, "1", "true", "on", "yes", "enabled",
     *                                           0, "0", "false", "off", "no" and "disabled"
     *                          Returns FALSE otherwise.
     */
    public static function isBool(mixed $variable): bool
    {
        return self::isEnabled($variable) !== null;
    }
}
