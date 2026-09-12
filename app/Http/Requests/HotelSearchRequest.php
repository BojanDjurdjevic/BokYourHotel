<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
class HotelSearchRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'city' => ['nullable','string','max:64'], 'country' => ['nullable','string','max:64'],
            'check_in' => ['nullable','required_with:check_out','date_format:Y-m-d','after_or_equal:today'],
            'check_out' => ['nullable','required_with:check_in','date_format:Y-m-d','after:check_in'],
            'adults' => ['nullable','integer','min:1','max:255'], 'children' => ['nullable','integer','min:0','max:255'],
            'stars' => ['nullable','array','max:5'], 'stars.*' => ['integer','between:1,5'],
            'hotel_facilities' => ['nullable','array','max:15'], 'hotel_facilities.*' => ['string','max:64'],
            'room_facilities' => ['nullable','array','max:15'], 'room_facilities.*' => ['integer','exists:facilities,id'],
            'board_type' => ['nullable','integer','exists:board_types,id'],
            'min_price' => ['nullable','numeric','min:0','max:9999999999'], 'max_price' => ['nullable','numeric','min:0','max:9999999999'],
            'sort' => ['nullable',Rule::in(['recommended','price_asc','price_desc','stars'])],
            'page' => ['nullable','integer','min:1','max:100000'],
        ];
    }
    public function after(): array {
        return [function ($validator) {
            if (!$validator->errors()->hasAny(['check_in','check_out']) && $this->check_in && $this->check_out
                && Carbon::parse($this->check_in)->diffInDays(Carbon::parse($this->check_out)) > 30) {
                $validator->errors()->add('check_out', 'The maximum stay is 30 days.');
            }
            if ($this->country && !$this->city) $validator->errors()->add('city', 'Select a city with its country.');
            if ($this->filled('min_price') && $this->filled('max_price') && is_numeric($this->min_price) && is_numeric($this->max_price) && $this->max_price < $this->min_price) $validator->errors()->add('max_price', 'Maximum price must be at least the minimum.');
        }];
    }
}
