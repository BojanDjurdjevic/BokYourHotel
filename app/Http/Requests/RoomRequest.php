<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function after(): array
    {
        return [function (\Illuminate\Validation\Validator $validator) {
            $boards = $this->input('board_types', []);
            if (! is_array($boards)) return;
            $ids = array_keys($boards);
            if (\App\Models\BoardType::whereIn('id', $ids)->count() !== count($ids)) {
                $validator->errors()->add('board_types', 'Choose valid board options.');
            }
        }];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:32',

            'room_type_id' => 'required|exists:room_types,id',

            'bed_type_id' => 'required|exists:bed_types,id',

            'capacity' => 'required|integer|min:1|max:4294967295',

            'price_per_night' => 'required|numeric|min:0|max:99999999.99',

            'total_units' => 'required|integer|min:1|max:4294967295',

            'board_types' => 'nullable|array',

            //'board_types.*' => 'exists:board_types,id',

            'board_types.*.enabled' => 'sometimes|boolean',
            'board_types.*.price' => 'nullable|required_if:board_types.*.enabled,1|numeric|min:0|max:99999999.99',
            'board_types.*' => 'array', 

            'facilities' => 'nullable|array',
            'facilities.*' => 'integer|exists:facilities,id',
        ];
    }
}
