<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;

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

        // Bust the landing page's cached review list so the newly
        // approved review shows up immediately instead of waiting
        // up to 10 minutes for the cache to expire naturally.
        Cache::forget("landing_reviews_{$tenant->id}");

        return back()->with('success', 'Review approved successfully.');
    }

    public function reject($id)
    {
        $tenant = app('currentTenant');
        $review = Review::where('tenant_id', $tenant->id)->findOrFail($id);
        $this->authorize('approve', $review);
        $review->update(['status' => 'rejected']);

        Cache::forget("landing_reviews_{$tenant->id}");

        return back()->with('success', 'Review rejected successfully.');
    }
}
