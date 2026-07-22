<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', 'min:8', 'confirmed'],
            // confirmed otomatis cocokkan dengan field <name>_confirmation (confirmPassword)
        ];
    }

    public function messages()
    {
        return [
            'currentPassword.current_password' => 'Current password is incorrect.',
            'newPassword.min' => 'New password must be at least 8 characters.',
            'newPassword.confirmed' => 'New password confirmation does not match.',
        ];
    }
}
