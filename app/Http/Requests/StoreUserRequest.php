<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // default no_image.jpg dipakai jika kosong
            'password' => 'required|string|min:8|confirmed',
            'outlets' => 'nullable|array',
            'outlets.*' => 'exists:outlets,id',
        ];
    }
}
