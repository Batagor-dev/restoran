<?php

namespace App\Helpers;

class MenuHelper
{
    protected static array $activeCache = [];

    /**
     * Tentukan apakah menu atau submenunya sedang aktif.
     */
    public static function isActive($menu): bool
    {
        if (! $menu) {
            return false;
        }

        $id = $menu->id ?? $menu->uuid ?? spl_object_id($menu);

        return self::$activeCache[$id] ??= (
            self::matchesUrl($menu->href ?? null) || self::hasActiveChild($menu)
        );
    }

    /**
     * Cek kecocokan URL menu dengan request saat ini (persis atau subpath).
     */
    protected static function matchesUrl(?string $href): bool
    {
        if (empty($href) || $href === '#' || str_starts_with($href, 'javascript:')) {
            return false;
        }

        $host = parse_url($href, PHP_URL_HOST);
        if ($host && $host !== request()->getHost()) {
            return false;
        }

        $path = trim(parse_url($href, PHP_URL_PATH) ?? '', '/');

        return $path !== ''
            ? request()->is($path, "{$path}/*")
            : request()->is('/');
    }

    /**
     * Cek apakah ada submenu yang aktif secara rekursif.
     */
    protected static function hasActiveChild($menu): bool
    {
        $children = $menu->relationLoaded('children') ? $menu->children : $menu->children;

        return (bool) $children?->contains(fn ($child) => self::isActive($child));
    }

    /**
     * Reset cache status aktif.
     */
    public static function clearCache(): void
    {
        self::$activeCache = [];
    }
}
