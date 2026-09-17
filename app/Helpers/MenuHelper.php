<?php

namespace App\Helpers;

class MenuHelper
{
    protected static array $activeCache = [];

    public static function isActive($menu): bool
    {
        if (! $menu) {
            return false;
        }

        $id = $menu->id ?? $menu->uuid ?? spl_object_id($menu);
        if (isset(self::$activeCache[$id])) {
            return self::$activeCache[$id];
        }

        // Cek href
        if (! empty($menu->href) && url($menu->href) === url()->current()) {
            return self::$activeCache[$id] = true;
        }

        // Cek children (rekursif)
        if ($menu->relationLoaded('children')) {
            foreach ($menu->children as $child) {
                if (self::isActive($child)) {
                    return self::$activeCache[$id] = true;
                }
            }
        }

        return self::$activeCache[$id] = false;
    }

    public static function clearCache(): void
    {
        self::$activeCache = [];
    }
}
