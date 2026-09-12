<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddHotelRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:64',
            'country' => 'required|string|min:3|max:64',
            'city' => 'required|string|min:3|max:64',
            'address' => 'required|string|max:128',
            'description' => 'nullable|string|max:255',
            'facilities' => 'nullable|array',
            'facilities.*' => 'string',
        ];
    }
}
