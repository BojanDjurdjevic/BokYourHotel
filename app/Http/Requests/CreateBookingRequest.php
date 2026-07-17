<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'hotel_id' => [
                'required',
                'exists:hotels,id',
            ],

            'check_in' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'check_out' => [
                'required',
                'date',
                'after:check_in',
            ],

            'guest_name' => [
                'required',
                'string',
                'max:255',
            ],

            'guest_email' => [
                'required',
                'email',
                'max:255',
            ],

            'guest_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.room_id' => [
                'required',
                'exists:rooms,id',
            ],

            'items.*.board_type_id' => [
                'required',
                'exists:board_types,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.adults' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.children' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}
