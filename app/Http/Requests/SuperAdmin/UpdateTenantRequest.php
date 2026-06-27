<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
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
        $tenantId = $this->route('tenant')?->id;

        return [
            'business_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required', 'string', 'alpha_dash', 'max:50',
                Rule::unique('tenants', 'subdomain')->ignore($tenantId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'plan' => ['required', Rule::in(['free', 'basic', 'premium'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subdomain.unique' => 'This subdomain has already been taken.',
            'subdomain.alpha_dash' => 'The subdomain may only contain letters, numbers, dashes, and underscores.',
        ];
    }
}
