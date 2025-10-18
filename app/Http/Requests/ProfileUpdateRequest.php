<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all authenticated users to update their profile. Adjust if you have extra checks.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', 'unique:users,email,'.$this->user()->id],
            'phone_number' => ['string', 'max:255', 'unique:users,phone_number,'.$this->user()->id],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'], // 10MB limit
        ];
    }
}
