<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Mail\NewReviewMail;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Review;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display the review creation form.
     */
    public function create($subdomain, $appointmentId)
    {
        $tenant = app('customerTenant');

        $appointment = Appointment::with(['service', 'staff.user'])
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', Auth::guard('customer')->id())
            ->where('status', 'completed')
            ->findOrFail($appointmentId);

        // Check if the customer has already submitted a review
        $existingReview = Review::where('appointment_id', $appointmentId)
            ->where('customer_id', Auth::guard('customer')->id())
            ->first();

        if ($existingReview) {
            return redirect()->route('customer.appointments', $subdomain)
                ->with('info', 'You have already submitted a review for this appointment.');
        }

        return view('customer.reviews.create', compact('tenant', 'subdomain', 'appointment'));

    }

    /**
     * Store a newly submitted review.
     */
    public function store(Request $request, $subdomain, $appointmentId)
    {
        $tenant = app('customerTenant');

        $appointment = Appointment::where('tenant_id', $tenant->id)
            ->where('customer_id', Auth::guard('customer')->id())
            ->where('status', 'completed')
            ->findOrFail($appointmentId);

        // Verify if a review already exists
        $exists = Review::where('appointment_id', $appointmentId)
            ->where('customer_id', Auth::guard('customer')->id())
            ->exists();

        if ($exists) {
            return redirect()->route('customer.appointments', $subdomain)
                ->with('info', 'You have already submitted a review for this appointment.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:500',
        ]);

        try {
            $review = Review::create([
                'tenant_id' => $tenant->id,
                'customer_id' => Auth::guard('customer')->id(),
                'appointment_id' => $appointment->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending',
            ]);
        } catch (QueryException $e) {
            // DB unique constraint violation — double submit race condition
            return redirect()->route('customer.appointments', $subdomain)
                ->with('info', 'You have already submitted a review for this appointment.');
        }
        AuditLog::record(
            'review.received',
            Review::class,
            $review->id,
            [
                'customer_name' => Auth::guard('customer')->user()->name,
                'rating' => $request->rating,
                'comment' => \Str::limit($request->comment, 50),
            ],
            $tenant->id,
            'review'
        );
        try {
            $ownerEmail = $tenant->email ?? null;
            if ($ownerEmail) {
                // FIX: send() → queue() — customer ko wait nahi karna padega mail ke liye
                \Mail::to($ownerEmail)->queue(new NewReviewMail(
                    $tenant,
                    Auth::guard('customer')->user(),
                    $request->rating,
                    $request->comment
                ));
            }
        } catch (\Exception $e) {
            \Log::error('New review notification failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('customer.appointments', $subdomain)
            ->with('success', 'Your review has been submitted and is awaiting approval by the owner.');
    }
}
