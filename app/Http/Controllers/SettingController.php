<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Models\Setting;
use App\Services\ImageService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('Setting Access'), 403);

        return view('setting.form', [
            'action' => route('setting.store'),
            'title' => Setting::getValue('title'),
            'keyword' => implode(',', Setting::getValue('keyword', [])),
            'description' => Setting::getValue('description'),
            'author' => Setting::getValue('author'),
            'favicon' => Setting::getValue('favicon'),
        ]);
    }

    public function store(StoreSettingRequest $request, ImageService $imageService)
    {
        abort_if(Gate::denies('Setting Access'), 403);

        $payload = $request->except(['_token', 'favicon']);

        /* ---------- handle favicon ---------- */
        if ($request->hasFile('favicon')) {
            Setting::deleteOldFile('favicon');          // hapus lama
            $file = $request->file('favicon');
            $compressed = $imageService->compress($file);
            $filename = 'settings/'.uniqid().'.jpg';
            Storage::disk('public')->put($filename, $compressed);
            $payload['favicon'] = $filename;
        }

        /* ---------- keyword jadi array ---------- */
        $payload['keyword'] = array_filter(
            explode(',', strtolower($request->input('keyword', '')))
        );

        Setting::setValue($payload);

        return redirect()->route('setting.index')
            ->with('success', __('messages.settings_saved'));
    }
}
