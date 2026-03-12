<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:rooms,id',

            'inventory' => 'nullable|array',

            'inventory.*.date' => 'required|date',

            'inventory.*.available' => 'required|integer|min:0',

            'inventory.*.price' => 'required|numeric|min:0'
        ];
    }
}
