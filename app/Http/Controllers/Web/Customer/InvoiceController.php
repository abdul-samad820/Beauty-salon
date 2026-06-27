<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function download($subdomain, $id)
    {
        $tenant = app('customerTenant');
        $customer = Auth::guard('customer')->user();

        $appointment = Appointment::with(['service', 'staff.user', 'tenant'])
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        // GST Calculation (18%)

        if (! $appointment->gst_rate || ! $appointment->gst_amount) {
            $gstRate = 18;
            $baseAmount = round($appointment->amount / (1 + $gstRate / 100), 2);
            $gstAmount = round($appointment->amount - $baseAmount, 2);

            $appointment->update([
                'gst_rate' => $gstRate,
                'gst_amount' => $gstAmount,
            ]);
        } else {
            $gstRate = $appointment->gst_rate;
            $gstAmount = $appointment->gst_amount;
            $baseAmount = round($appointment->amount - $gstAmount, 2);
        }

        $invoiceNumber = 'INV-'.date('Y').'-'.str_pad($appointment->id, 5, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('customer.invoice', compact(
            'appointment',
            'tenant',
            'customer',
            'baseAmount',
            'gstAmount',
            'gstRate',
            'invoiceNumber'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("LUMIERE-Invoice-{$invoiceNumber}.pdf");
    }
}
