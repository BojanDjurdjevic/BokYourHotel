<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddHotelRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string'
        ];
    }
}
