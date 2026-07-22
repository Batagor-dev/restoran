<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // 🚨 WAJIB: cek verifikasi email dulu
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // ADMIN / SUPER ADMIN
        if ($user->hasRole(['Admin', 'Super Admin'])) {
            return redirect('/dashboard');
        }

        // USER
        return redirect('/');
    }
}
