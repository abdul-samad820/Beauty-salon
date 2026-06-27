@component('mail::message')
# New Client Feedback ✦

Hello **{{ $tenantName }}** Team,

A new customer feedback has been submitted for your salon. Here are the review details:

@component('mail::panel')
**Client:** {{ $customerName }}
**Rating:** {{ str_repeat('★', $rating) }}{{ str_repeat('☆', 5 - $rating) }} ({{ $rating }}/5)

**Comment:** _"{{ $comment }}"_
@endcomponent

Please verify this review and approve or reject it from your dashboard.

@component('mail::button', ['url' => route('owner.reviews.index')])
Manage Review
@endcomponent

Elevating your salon experience,<br>
**LUMIÈRE Platform**
@endcomponent
