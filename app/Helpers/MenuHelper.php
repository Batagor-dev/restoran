<?php

namespace App\Helpers;

class MenuHelper
{
    public static function isActive($menu): bool
    {
        // Cek href
        if (! empty($menu->href) && url($menu->href) === url()->current()) {
            return true;
        }

        // Cek children (rekursif)
        foreach ($menu->children as $child) {
            if (self::isActive($child)) {
                return true;
            }
        }

        return false;
    }
}
