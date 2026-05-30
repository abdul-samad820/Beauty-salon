<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->route('tenant')?->id;

        return [
            'business_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required', 'string', 'alpha_dash', 'max:50',
                Rule::unique('tenants', 'subdomain')->ignore($tenantId),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'plan' => ['required', Rule::in(['free', 'pro', 'enterprise'])],
        ];
    }

    public function messages(): array
    {
        return [
            'subdomain.unique' => 'Ye subdomain pehle se le liya gaya hai.',
            'subdomain.alpha_dash' => 'Subdomain mein sirf letters, numbers aur hyphens allowed hain.',
        ];
    }
}
