<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|uuid|exists:categories,id'
        ];
    }
} 