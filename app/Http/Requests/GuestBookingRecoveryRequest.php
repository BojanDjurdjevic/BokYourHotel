<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestBookingRecoveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_number' => ['required', 'string', 'max:64'],
            'email' => ['required', 'email', 'max:254'],
        ];
    }

    public function attributes(): array
    {
        return [
            'booking_number' => 'booking number',
            'email' => 'email address',
        ];
    }
}
