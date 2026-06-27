<?php

namespace App\Http\Requests\Customer;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure user is properly authenticated inside the explicit customer context guard loop
        return auth('customer')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // FIXED SEC-010: Streamlined fallback resolution to retrieve absolute tenant model safely from verified instances
        $tenant = app('currentTenant') ?? app('customerTenant');
        $tenantId = $tenant?->id;

        // FIXED SEC-021: Pull the tenant specific timezone dynamically to parse absolute today baseline threshold bounds safely
        $tenantTimezone = $tenant?->settings['timezone'] ?? config('app.timezone', 'UTC');
        $tenantTodayDate = Carbon::now($tenantTimezone)->toDateString();

        return [
            'service_id' => [
                'required',
                // Enforced localized explicit tenant filtering over validation thread scopes
                Rule::exists('services', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)->where('is_active', true);
                }),
            ],

            'staff_id' => [
                'nullable',
                // FIXED: Matched column constraint rules using strict boolean availability structure mapping fields instead of raw status strings
                Rule::exists('staff', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                        ->where('is_available', true);
                }),
            ],

            'appointment_date' => [
                'required',
                'date',
                // FIXED SEC-021: Enforced strict validation timeline bounds using timezone-aware real dates instead of hardcoded strings
                'after_or_equal:'.$tenantTodayDate,
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'payment_method' => [
                'required',
                'in:cash,upi,razorpay',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'service_id.exists' => 'The selected service profile is either invalid or currently unavailable.',
            'staff_id.exists' => 'The selected service provider is not available for the requested slot allocation.',
            'appointment_date.after_or_equal' => 'The appointment date cannot be scheduled in the past.',
            'start_time.date_format' => 'The start time field must strictly adhere to standard 24-hour HH:MM notation limits.',
        ];
    }
}
