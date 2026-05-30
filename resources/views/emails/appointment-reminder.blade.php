@component('mail::message')
# Appointment Reminder 💅

Namaste **{{ $customerName }}**,

Aapka appointment **2 ghante baad** hai!

@component('mail::panel')
**Service:** {{ $serviceName }}
**Staff:** {{ $staffName }}
**Date:** {{ $appointmentDate }}
**Time:** {{ $startTime }} - {{ $endTime }}
@endcomponent

@component('mail::button', ['url' => ''])
Appointment Dekho
@endcomponent

Koi problem ho toh parlour se contact karein.

Shukriya,
{{ config('app.name') }}
@endcomponent