<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',

            'room_type_id' => 'required|exists:room_types,id',

            'bed_type_id' => 'required|exists:bed_types,id',

            'capacity' => 'required|integer|min:1',

            'price_per_night' => 'required|numeric|min:0',

            'total_units' => 'required|integer|min:1',

            'board_types' => 'nullable|array',

            'board_types.*' => 'exists:board_types,id',
        ];
    }
}
