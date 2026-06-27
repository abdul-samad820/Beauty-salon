<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewWebController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');

        $reviews = Review::with(['customer', 'appointment.service'])
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(15);

        $pendingCount = Review::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->count();

        return view('owner.reviews.index', compact('reviews', 'pendingCount'));
    }

    public function approve($id)
    {
        $tenant = app('currentTenant');
        $review = Review::where('tenant_id', $tenant->id)->findOrFail($id);
        $this->authorize('approve', $review);
        $review->update(['status' => 'approved']);

        return back()->with('success', 'Review approved successfully.');
    }

    public function reject($id)
    {
        $tenant = app('currentTenant');
        $review = Review::where('tenant_id', $tenant->id)->findOrFail($id);
        $this->authorize('approve', $review);
        $review->update(['status' => 'rejected']);

        return back()->with('success', 'Review rejected successfully.');
    }
}
