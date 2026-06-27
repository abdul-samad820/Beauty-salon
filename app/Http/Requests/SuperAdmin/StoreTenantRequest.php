<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'alpha_dash',
                'max:50',
                'unique:tenants,subdomain',
                Rule::notIn(['login', 'logout', 'owner', 'superadmin', 'staff', 'email', 'health', 'register']),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'plan' => ['required', Rule::in(['free', 'basic', 'premium'])],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_password' => ['required', 'min:8', 'confirmed'],
            'owner_password_confirmation' => ['required'],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'business_name.required' => 'The business name is required.',
            'subdomain.required' => 'The subdomain is required.',
            'subdomain.unique' => 'This subdomain has already been taken.',
            'subdomain.alpha_dash' => 'The subdomain may only contain letters, numbers, dashes, and underscores.',
            'owner_email.unique' => 'An account with this email address already exists.',
            'owner_password.min' => 'The password must be at least 8 characters long.',
            'owner_password.confirmed' => 'The password confirmation does not match.',
            'plan.in' => 'The selected plan is invalid.',
            'subdomain.not_in' => 'This subdomain name is reserved and cannot be used.',
            'plan.in' => 'Please select a valid plan: Free, Basic, or Premium.',
        ];
    }
}
