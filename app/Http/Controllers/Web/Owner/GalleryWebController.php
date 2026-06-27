<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GalleryWebController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');
        $images = GalleryImage::where('tenant_id', $tenant->id)
            ->orderBy('sort_order')
            ->get();

        return view('owner.gallery.index', compact('images'));
    }

    public function store(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:3048',
            'caption' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $imageInfo = @getimagesize($file->getRealPath());

        if ($imageInfo === false) {
            return back()->withErrors(['image' => 'Uploaded file is not a valid image.']);
        }

        $path = $request->file('image')->store('gallery', 'public');

        GalleryImage::create([
            'tenant_id' => $tenant->id,
            'image' => $path,
            'caption' => $request->caption,
            'sort_order' => GalleryImage::where('tenant_id', $tenant->id)->count() + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Image uploaded successfully.');
    }

    public function destroy($id)
    {
        $tenant = app('currentTenant');
        $image = GalleryImage::where('tenant_id', $tenant->id)->findOrFail($id);

        \Storage::disk('public')->delete($image->image);
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'order' => 'required|array',
            'order.*' => ['integer', Rule::exists('gallery_images', 'id')->where('tenant_id', $tenant->id)],
        ]);

        $caseWhen = collect($request->order)
            ->map(fn ($id, $i) => 'WHEN '.(int) $id.' THEN '.($i + 1))
            ->join(' ');

        \DB::table('gallery_images')
            ->where('tenant_id', $tenant->id)
            ->whereIn('id', $request->order)
            ->update(['sort_order' => \DB::raw("CASE id $caseWhen END")]);

        return response()->json(['success' => true]);
    }
}
