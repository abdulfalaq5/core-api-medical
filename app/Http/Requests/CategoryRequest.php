<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-]+$/'
            ]
        ];

        // Add unique validation rule
        if ($this->isMethod('POST')) {
            $rules['name'][] = Rule::unique('categories', 'name')->whereNull('deleted_at');
        } else {
            $rules['name'][] = Rule::unique('categories', 'name')->whereNull('deleted_at')->ignore($this->route('id'));
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori harus diisi',
            'name.string' => 'Nama kategori harus berupa teks',
            'name.max' => 'Nama kategori maksimal 255 karakter',
            'name.regex' => 'Nama kategori hanya boleh mengandung huruf, angka, spasi, dan tanda hubung',
            'name.unique' => 'Nama kategori sudah digunakan'
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422));
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->count() > 0) {
                throw new \Illuminate\Validation\ValidationException($validator, response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422));
            }
        });
    }

    public function prepareForValidation()
    {
        $value = $this->input('name');
        
        // Basic SQL injection patterns
        if (preg_match('/[\'";]/', $value) || 
            preg_match('/\b(drop|delete|update|insert|select|alter|create|truncate)\b/i', $value) ||
            preg_match('/\b(union|join|where|from|into)\b/i', $value) ||
            preg_match('/\b(table|database|schema)\b/i', $value) ||
            preg_match('/\b(or|and)\s+[\'"0-9]/', $value) ||
            preg_match('/--|#|\/\*|\*\//', $value)) {
            
            throw new \Illuminate\Validation\ValidationException(
                Validator::make(
                    ['name' => $value],
                    ['name' => 'required'],
                    ['name.required' => 'The name contains invalid characters or SQL keywords.']
                ),
                response()->json([
                    'status' => 'error',
                    'errors' => ['name' => ['The name contains invalid characters or SQL keywords.']]
                ], 422)
            );
        }
    }
} 