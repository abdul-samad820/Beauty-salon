@component('mail::message')

<div style="text-align:center;margin-bottom:2rem;">
    <div style="font-family:Georgia,serif;font-size:2rem;font-weight:400;letter-spacing:0.15em;color:#c9a96e;">
        LUMIÈRE<span style="color:#c9a96e;">.</span>
    </div>
    <div style="font-size:0.75rem;letter-spacing:0.2em;color:#999;text-transform:uppercase;margin-top:4px;">
        Beauty Management
    </div>
</div>

# Booking Confirmed!

Hello **{{ $customerName }}**,

Your booking has been successfully confirmed! Please find the details below:

@component('mail::panel')
| | |
|---|---|
| **Booking ID** | #{{ $appointmentId }} |
| **Service** | {{ $serviceName }} |
| **Staff** | {{ $staffName }} |
| **Date** | {{ $appointmentDate }} |
| **Time** | {{ $startTime }} – {{ $endTime }} |
| **Parlour** | {{ $parlourName }} |
| **Amount** | ₹{{ $amount }} |
@endcomponent

**Important Reminders:**
- Please arrive **10 minutes before** your scheduled time.
- If you need to cancel, please do so at least **2 hours prior** to the appointment.
- If you have any questions, please contact your parlour directly.

@component('mail::button', ['url' => route('customer.appointments'), 'color' => 'success'])
View My Bookings
@endcomponent

We look forward to seeing you soon!

**The {{ $parlourName }} Team**<br>
*Powered by LUMIÈRE*

---
<div style="font-size:0.7rem;color:#999;text-align:center;">
    This is an automated confirmation email.
</div>

@endcomponent
