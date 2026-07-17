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
                'max:20'
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

    public function messages(): array
    {
        return [

            'hotel_id.required' => 'Please select a hotel.',
            'hotel_id.exists' => 'Selected hotel does not exist.',

            'check_in.required' => 'Please select a check-in date.',
            'check_in.date' => 'Check-in must be a valid date.',
            'check_in.after_or_equal' => 'Check-in cannot be in the past.',

            'check_out.required' => 'Please select a check-out date.',
            'check_out.date' => 'Check-out must be a valid date.',
            'check_out.after' => 'Check-out must be after check-in.',

            'guest_name.required' => 'Guest name is required.',
            'guest_name.max' => 'Guest name is too long.',

            'guest_email.required' => 'Guest email is required.',
            'guest_email.email' => 'Please enter a valid email address.',

            'guest_phone.max' => 'Phone number is too long.',

            'notes.max' => 'Notes cannot exceed 2000 characters.',

            'items.required' => 'Please select at least one room.',
            'items.array' => 'Invalid booking data.',
            'items.min' => 'Please select at least one room.',

            'items.*.room_id.required' => 'Please select a room.',
            'items.*.room_id.exists' => 'Selected room does not exist.',

            'items.*.board_type_id.required' => 'Please select a board type.',
            'items.*.board_type_id.exists' => 'Selected board type does not exist.',

            'items.*.quantity.required' => 'Please enter the number of rooms.',
            'items.*.quantity.integer' => 'Room quantity must be a whole number.',
            'items.*.quantity.min' => 'At least one room must be booked.',
            'items.*.quantity.max' => 'You cannot book more than 20 rooms at once.',

            'items.*.adults.required' => 'Please enter the number of adults.',
            'items.*.adults.integer' => 'Adults must be a whole number.',
            'items.*.adults.min' => 'At least one adult is required.',

            'items.*.children.integer' => 'Children must be a whole number.',
            'items.*.children.min' => 'Children cannot be negative.',
        ];
    }

    public function attributes(): array
    {
        return [
            'guest_name' => 'guest name',
            'guest_email' => 'guest email',
            'guest_phone' => 'guest phone',
            'check_in' => 'check-in date',
            'check_out' => 'check-out date',
            'items' => 'rooms',
            'items.*.room_id' => 'room',
            'items.*.board_type_id' => 'board type',
            'items.*.quantity' => 'room quantity',
            'items.*.adults' => 'number of adults',
            'items.*.children' => 'number of children',
        ];
    }
}
