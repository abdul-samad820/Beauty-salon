<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class CustomerPaymentController extends Controller
{
    private Api $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    // ── Create Razorpay Order for Appointment ─────────────────────
    public function createOrder(Request $request, string $subdomain, int $appointmentId)
    {
        $tenant = app('customerTenant');
        $customer = auth('customer')->user();

        // Appointment validate karo — sirf is customer ka aur is tenant ka
        $appointment = Appointment::with('service')
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->where('payment_method', 'razorpay')
            ->whereIn('payment_status', ['pending', 'failed'])
            ->findOrFail($appointmentId);

        $amountInPaise = (int) ($appointment->amount * 100);

        // Amount zero nahi honi chahiye
        if ($amountInPaise <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment amount.',
            ], 422);
        }

        try {
            $order = $this->razorpay->order->create([
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'appt_'.$appointment->id.'_'.time(),
                'notes' => [
                    'appointment_id' => $appointment->id,
                    'tenant_id' => $tenant->id,
                    'service' => $appointment->service->name,
                    'customer_name' => $customer->name,
                ],
            ]);

            // Order ID save karo
            $appointment->razorpay_order_id = $order->id;
            $appointment->save();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'key_id' => config('services.razorpay.key_id'),
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'service_name' => $appointment->service->name,
                'tenant_name' => $tenant->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Appointment Razorpay order creation failed', [
                'appointment_id' => $appointment->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed. Please try again.',
            ], 500);
        }
    }

    // ── Verify Payment & Confirm Appointment ──────────────────────
    public function verifyPayment(Request $request, string $subdomain, int $appointmentId)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $tenant = app('customerTenant');
        $customer = auth('customer')->user();

        $appointment = Appointment::with('service')
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->where('razorpay_order_id', $request->razorpay_order_id)
            ->findOrFail($appointmentId);

        // ── Signature Verification ────────────────────────────────
        try {
            $this->razorpay->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);
        } catch (SignatureVerificationError $e) {
            Log::warning('Appointment payment signature verification failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            $appointment->payment_status = 'failed';
            $appointment->save();

            return redirect()->route('customer.book.confirmed', [$subdomain, $appointment->id])
                ->with('error', 'Payment verification failed. Please contact support if amount was deducted.');
        }

        // ── Duplicate Payment Guard ───────────────────────────────
        if ($appointment->payment_status === 'paid') {
            return redirect()->route('customer.appointments', $subdomain)
                ->with('success', 'Payment already confirmed.');
        }

        // ── Mark Payment Success ──────────────────────────────────
        $appointment->update([
            'payment_status' => 'paid',
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => hash('sha256', $request->razorpay_signature),

            'status' => 'pending', // Owner confirm karega
        ]);

        Log::info('Appointment payment successful', [
            'appointment_id' => $appointment->id,
            'payment_id' => $request->razorpay_payment_id,
            'amount' => $appointment->amount,
        ]);

        return redirect()->route('customer.appointments', $subdomain)
            ->with('success', "Payment of ₹{$appointment->amount} confirmed! Your appointment is booked.");
    }
}
