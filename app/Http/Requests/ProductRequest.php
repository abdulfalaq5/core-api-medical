<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'sku' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9\s\-]+$/'
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-]+$/'
            ],
            'price' => [
                'required',
                'numeric',
                'min:0'
            ],
            'stock' => [
                'required',
                'integer',
                'min:0'
            ],
            'category_id' => [
                'required',
                'string',
                'uuid',
                Rule::exists('categories', 'id')->whereNull('deleted_at')
            ]
        ];

        if ($this->isMethod('POST')) {
            $rules['sku'][] = Rule::unique('products', 'sku')->whereNull('deleted_at');
        } else {
            $rules['sku'][] = Rule::unique('products', 'sku')->whereNull('deleted_at')->ignore($this->route('id'));
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'SKU produk harus diisi',
            'sku.string' => 'SKU produk harus berupa teks',
            'sku.regex' => 'SKU produk hanya boleh mengandung huruf, angka, spasi, dan tanda hubung',
            'sku.unique' => 'SKU produk sudah digunakan',
            'name.required' => 'Nama produk harus diisi',
            'name.string' => 'Nama produk harus berupa teks',
            'name.max' => 'Nama produk maksimal 255 karakter',
            'name.regex' => 'Nama produk hanya boleh mengandung huruf, angka, spasi, dan tanda hubung',
            'price.required' => 'Harga produk harus diisi',
            'price.numeric' => 'Harga produk harus berupa angka',
            'price.min' => 'Harga produk minimal 0',
            'stock.required' => 'Stok produk harus diisi',
            'stock.integer' => 'Stok produk harus berupa bilangan bulat',
            'stock.min' => 'Stok produk minimal 0',
            'category_id.required' => 'Kategori produk harus diisi',
            'category_id.uuid' => 'Format kategori produk tidak valid',
            'category_id.exists' => 'Kategori produk tidak ditemukan'
        ];
    }
} 