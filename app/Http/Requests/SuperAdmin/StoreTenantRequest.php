<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:tenants,subdomain'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'plan' => ['required', Rule::in(['free', 'pro', 'enterprise'])],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_password' => ['required', 'min:8', 'confirmed'],
            'owner_password_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_name.required' => 'Parlour ka naam zaroori hai.',
            'subdomain.required' => 'Subdomain zaroori hai.',
            'subdomain.unique' => 'Ye subdomain pehle se le liya gaya hai.',
            'subdomain.alpha_dash' => 'Subdomain mein sirf letters, numbers aur hyphens allowed hain.',
            'owner_email.unique' => 'Is email se pehle se ek account hai.',
            'owner_password.min' => 'Password kam se kam 8 characters ka hona chahiye.',
            'owner_password.confirmed' => 'Dono passwords match nahi karte.',
            'plan.in' => 'Invalid plan select kiya hai.',
        ];
    }
}
