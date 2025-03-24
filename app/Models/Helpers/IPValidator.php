<?php

declare(strict_types=1);

namespace App\Models\Helpers;

use Nette\InvalidArgumentException;

class IPValidator
{
    /**
     * Checks if an IP address or subnet is contained within a list of allowed IPs and subnets.
     *
     * @param string $needle The IP address or subnet to check (e.g., "192.168.0.10" or "192.168.0.0/24").
     * @param array<string> $haystack List of allowed IPs and subnets (e.g., ["127.0.0.1", "192.168.0.0/24"]).
     * @return bool True if the IP or subnet is in the allowed list, false otherwise.
     */
    public static function ipInList(string $needle, array $haystack): bool
    {
        // Convert needle IP to binary and determine if it's a subnet
        [$needleIp, $needleMask] = self::parseIpAndMask($needle);

        foreach ($haystack as $entry) {
            [$entryIp, $entryMask] = self::parseIpAndMask($entry);

            if ($needleMask >= $entryMask && self::isIpInSubnet($needleIp, $needleMask, $entryIp, $entryMask)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parses an IP address and mask from a given string.
     *
     * @param string $ipMaskString The input string (e.g., "192.168.0.1" or "192.168.0.0/24").
     * @return array{string|false,int} An array with two elements: binary IP and integer mask.
     */
    public static function parseIpAndMask(string $ipMaskString): array
    {
        $ipMaskString = trim($ipMaskString);
        $parts = explode('/', $ipMaskString);
        $ip = $parts[0];
        $mask = isset($parts[1]) ? (int)$parts[1] : (strpos($ip, ':') === false ? 32 : 128);

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException("Invalid IP address: $ip");
        }

        return [inet_pton($ip), $mask];
    }

    /**
     * Checks if a given IP or subnet is within another subnet.
     *
     * @param string $needleIp Binary representation of the IP to check.
     * @param int $needleMask The mask of the IP to check.
     * @param string $subnetIp Binary representation of the subnet IP.
     * @param int $subnetMask The subnet mask.
     * @return bool True if $needleIp/$needleMask is within $subnetIp/$subnetMask.
     */
    public static function isIpInSubnet(string $needleIp, int $needleMask, string $subnetIp, int $subnetMask): bool
    {
        // Subnet mask must be smaller or equal (e.g., /24 can contain /25, but not vice versa)
        if ($needleMask < $subnetMask) {
            return false;
        }

        // Calculate subnet mask in binary
        $mask = str_repeat("1", $subnetMask) . str_repeat("0", (strlen($needleIp) * 8) - $subnetMask);
        $maskBin = pack("H*", str_pad(base_convert($mask, 2, 16), strlen($needleIp) * 2, "0", STR_PAD_LEFT));

        // Apply the subnet mask and compare
        return ($needleIp & $maskBin) === ($subnetIp & $maskBin);
    }

}
