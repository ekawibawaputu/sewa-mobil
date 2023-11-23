<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'status' => 'required|string|in:pending,confirmed, done',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string|in:pending,success,failed,expired',
            'payment_url' => 'nullable|string|max:255',
            'item_id' => 'required|integer|exists:items,id',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}
