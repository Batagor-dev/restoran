<?php
 
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('settings')) {
    function settings()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $cached = Cache::rememberForever('app_settings', function () {
            $settings = Setting::all()->keyBy('key');

            $getValue = function (string $key, mixed $default = null) use ($settings) {
                if (! $settings->has($key)) {
                    return $default;
                }
                $row = $settings->get($key);

                return $row->serialize ? json_decode($row->value, true) : $row->value;
            };

            $keyword = $getValue('keyword');
            if (is_array($keyword)) {
                $keyword = implode(',', $keyword);
            }

            return [
                'title' => $getValue('title'),
                'keyword' => $keyword,
                'description' => $getValue('description'),
                'author' => $getValue('author'),
                'favicon' => $getValue('favicon'),
            ];
        });

        return $cached;
    }
}
