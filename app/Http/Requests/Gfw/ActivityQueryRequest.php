<?php

namespace App\Http\Requests\Gfw;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActivityQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'region' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'refresh' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date_format' => 'Format start_date harus berupa YYYY-MM-DD.',
            'end_date.date_format' => 'Format end_date harus berupa YYYY-MM-DD.',
            'end_date.after_or_equal' => 'start_date tidak boleh lebih besar daripada end_date.',
            'limit.max' => 'Batas maksimal query adalah 100 record.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'source' => 'global_fishing_watch',
            'error' => $validator->errors()->first(),
            'errors' => $validator->errors(),
            'status' => 422,
        ], 422));
    }
}
