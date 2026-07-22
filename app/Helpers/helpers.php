<?php

use App\Models\Setting;

if (! function_exists('settings')) {
    function settings()
    {
        $data['title'] = Setting::getValue('title');
        $keyword = Setting::getValue('keyword');
        if (is_array($keyword)) {
            $keyword = implode(',', $keyword);
        }
        $data['keyword'] = $keyword;
        $data['description'] = Setting::getValue('description');
        $data['author'] = Setting::getValue('author');
        $data['favicon'] = Setting::getValue('favicon');

        return $data;
    }
}
