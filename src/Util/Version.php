<?php

namespace App\Util;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;

class Version
{
    public static function isNew(string $version, string $current_version): bool|int
    {
        return Comparator::greaterThan(self::normalize($version), self::normalize($current_version));
    }

    public static function isNewAndSameMinor(string $version, string $current_version): bool|int
    {
        return self::isNew($version, $current_version) && self::isSameMinor($version, $current_version);
    }

    public static function isSameMinor(string $version, string $current_version): bool
    {
        return self::minor($version) === self::minor($current_version);
    }

    public static function normalize(string $version): string
    {
        $version = str_replace('8.x-', '', $version);

        return (new VersionParser())->normalize($version);
    }

    public static function clean(string $version): string
    {
        return self::normalize($version);
    }

    public static function minor(string $version): string
    {
        $parts = explode('.', self::normalize($version));
        return sprintf('%d.%d', $parts[0], $parts[1]);
    }

    public static function patch(string $version): string
    {
        $parts = explode('.', self::normalize($version));
        return sprintf('%d.%d.%d', $parts[0], $parts[1], $parts[2]);
    }
}
