<?php

namespace App\Http\Requests\Gfw;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VesselSearchRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'min:2', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'refresh' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'query.min' => 'Parameter query minimal 2 karakter.',
            'query.max' => 'Parameter query maksimal 100 karakter.',
            'limit.integer' => 'Parameter limit harus berupa angka bulat.',
            'limit.min' => 'Parameter limit minimal 1 record.',
            'limit.max' => 'Parameter limit maksimal 100 record.',
            'offset.integer' => 'Parameter offset harus berupa angka bulat.',
            'offset.min' => 'Parameter offset minimal 0.',
            'start_date.date' => 'Format start_date tidak valid.',
            'end_date.date' => 'Format end_date tidak valid.',
        ];
    }

    /**
     * Handle failed validation and return standard JSON envelope.
     */
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
