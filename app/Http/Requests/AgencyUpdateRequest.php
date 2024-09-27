<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgencyUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'user_id' => 'nullable|integer|exists:users,id',
            'agency_name' => 'string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'agency_license' => 'nullable|string',
            'address' => 'nullable|string',
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'website' => 'nullable|string',
        ];
    }
}
