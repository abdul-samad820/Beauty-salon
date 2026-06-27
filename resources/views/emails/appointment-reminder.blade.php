@component('mail::message')

<div style="text-align:center;margin-bottom:2rem;">
    <div style="font-family:Georgia,serif;font-size:2rem;font-weight:400;letter-spacing:0.15em;color:#c9a96e;">
        LUMIÈRE<span style="color:#c9a96e;">.</span>
    </div>
    <div style="font-size:0.75rem;letter-spacing:0.2em;color:#999;text-transform:uppercase;margin-top:4px;">
        Beauty Management
    </div>
</div>

# Appointment Reminder

Hello **{{ $customerName }}**,

This is a friendly reminder that you have an appointment scheduled in **2 hours**. Please find the details below:

@component('mail::panel')
| | |
|---|---|
| **Service** | {{ $serviceName }} |
| **Staff** | {{ $staffName }} |
| **Date** | {{ $appointmentDate }} |
| **Time** | {{ $startTime }} – {{ $endTime }} |
@endcomponent

**Important Reminders:**
- Please arrive **10 minutes before** your appointment.
- If you need to cancel, please do so via the app.
- If you have any questions, please contact the parlour directly.

@component('mail::button', ['url' => route('customer.appointments'), 'color' => 'primary'])
View My Appointments
@endcomponent

We look forward to seeing you soon!

**The LUMIÈRE Team**

---
<div style="font-size:0.7rem;color:#999;text-align:center;">
    This is an automated reminder email. Please do not reply to this message.
</div>

@endcomponent
