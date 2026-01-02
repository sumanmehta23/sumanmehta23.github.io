<?php

namespace App\Http\Requests\Zapier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateZapierAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all requests (webhook from Zapier)
        return true;
    }

    /**
     * Handle a failed validation attempt (return JSON for API)
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:aspnetusers,email|max:255',
            'phone' => 'required|string|max:20',
            'account_type' => 'nullable|string|in:Demo,Live,Standard', // Demo Standard or Live
            'group_code' => 'nullable|string|max:50', // MT5 group code if needed
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'This email is already registered',
            'email.max' => 'Email cannot exceed 255 characters',
            'phone.required' => 'Phone number is required',
            'phone.string' => 'Phone must be a string',
            'phone.max' => 'Phone cannot exceed 20 characters',
            'account_type.in' => 'Account type must be Demo, Live, or Standard',
            'group_code.max' => 'Group code cannot exceed 50 characters',
        ];
    }

    /**
     * Get the sanitized input data
     */
    public function sanitize(): array
    {
        return [
            'name' => trim($this->input('name')),
            'email' => trim(strtolower($this->input('email'))),
            'phone' => trim($this->input('phone')),
            'account_type' => $this->input('account_type', 'Standard'),
            'group_code' => $this->input('group_code'),
        ];
    }

    /**
     * Prepare the data for validation by merging sanitized input.
     */
    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitize());
    }
}
