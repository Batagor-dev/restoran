<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Mengarahkan user ke halaman login Google.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback dari Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->id,
                    'foto' => $googleUser->avatar,
                ]);
            } else {
                // Buat username unik dari email (contoh: budi@gmail.com jadi budi_123)
                $username = explode('@', $googleUser->email)[0].Str::lower(Str::random(4));

                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'username' => $username, // WAJIB DIISI
                    'google_id' => $googleUser->id,
                    'foto' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(), // Langsung verifikasi karena dari Google
                ]);
            }

            Auth::login($user);

            return redirect()->intended(config('fortify.home'));

        } catch (\Exception $e) {
            // Debug: Log error agar Anda tahu alasan pastinya di storage/logs/laravel.log
            \Log::error('Google Login Error: '.$e->getMessage());

            return redirect('/login')->with('error', __('messages.login_failed', ['error' => $e->getMessage()]));
        }
    }
}
