<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|max:100',
            'description' => 'required',
            'category_id' => 'required',
            'price' => 'required|numeric',
            'weight' => 'required|numeric',
            'status' => 'required|in:active,inactive,draft',
            'image' => 'image|mimes:jpeg,png,jpg|max:5000'
        ];
    }
}
