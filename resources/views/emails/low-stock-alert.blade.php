@component('mail::message')

<div style="text-align:center;margin-bottom:2rem;">
    <div style="font-family:Georgia,serif;font-size:2rem;font-weight:400;letter-spacing:0.15em;color:#c9a96e;">
        LUMIÈRE<span style="color:#c9a96e;">.</span>
    </div>
</div>

# Low Stock Alert

Hello **{{ $ownerName }}**,

Stock for a product at **{{ $parlourName }}** is running low and has fallen below your configured threshold:

@component('mail::panel')
| | |
|---|---|
| **Product** | {{ $productName }} |
| **Current Stock** | {{ $currentStock }} {{ $unit ?? 'units' }} |
| **Minimum Threshold** | {{ $threshold }} {{ $unit ?? 'units' }} |
| **Status** | Low Stock |
@endcomponent

Please replenish your stock soon to ensure appointments are not affected.

@component('mail::button', ['url' => route('owner.inventory.index'), 'color' => 'error'])
Manage Inventory
@endcomponent

**LUMIÈRE System**

---
<div style="font-size:0.7rem;color:#999;text-align:center;">
    This is an automated alert. To adjust your inventory thresholds, please visit Owner Panel > Inventory.
</div>

@endcomponent
