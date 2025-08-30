<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Email verification request validation.
 *
 * Validates email verification parameters.
 */
class VerifyEmailRequest extends FormRequest
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
        return [
            'id' => ['required', 'integer', 'exists:users,id'],
            'hash' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => __('validation.required', ['attribute' => __('validation.attributes.id')]),
            'id.integer' => __('validation.integer', ['attribute' => __('validation.attributes.id')]),
            'id.exists' => __('validation.exists', ['attribute' => __('validation.attributes.id')]),
            'hash.required' => __('validation.required', ['attribute' => __('validation.attributes.hash')]),
        ];
    }
}
