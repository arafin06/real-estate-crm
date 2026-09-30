<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with('primaryImage')
            ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->search, fn ($q, $v) => $q->where(fn ($q) => $q->where('title', 'like', "%{$v}%")
                ->orWhere('address', 'like', "%{$v}%")))
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->when($request->listing_type, fn ($q, $v) => $q->where('listing_type', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->city, fn ($q, $v) => $q->where('city', 'like', "%{$v}%"))
            ->when($request->min_price, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($request->max_price, fn ($q, $v) => $q->where('price', '<=', $v))
            ->latest();

        return response()->json($query->paginate(min((int) $request->input('per_page', 12), 100)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:residential,commercial',
            'listing_type' => 'required|in:sale,rent',
            'price' => 'required|numeric|min:0',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'nullable|integer|min:0',
            'status' => 'required|in:active,under_contract,sold,off_market',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'primary_index' => 'nullable|integer',
        ]);

        $property = Property::create(array_merge(
            collect($data)->except(['images', 'primary_index'])->toArray(),
            ['user_id' => auth()->id()]
        ));

        if ($request->hasFile('images')) {
            $primaryIndex = (int) ($request->primary_index ?? 0);
            foreach ($request->file('images') as $i => $image) {
                $path = $image->store("properties/{$property->id}", 'public');
                $property->images()->create([
                    'image_path' => $path,
                    'is_primary' => $i === $primaryIndex,
                ]);
            }
        }

        return response()->json($property->load('images'), 201);
    }

    public function show(Property $property)
    {
        $this->gate($property);

        return response()->json($property->load('images'));
    }

    public function update(Request $request, Property $property)
    {
        $this->gate($property);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:residential,commercial',
            'listing_type' => 'sometimes|required|in:sale,rent',
            'price' => 'sometimes|required|numeric|min:0',
            'address' => 'sometimes|required|string',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'zip' => 'sometimes|required|string|max:20',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'nullable|integer|min:0',
            'status' => 'sometimes|required|in:active,under_contract,sold,off_market',
        ]);

        $property->update($data);

        return response()->json($property->load('images'));
    }

    public function destroy(Property $property)
    {
        $this->gate($property);

        foreach ($property->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $property->delete();

        return response()->json(['message' => 'Property deleted.']);
    }

    // --- Image endpoints ---

    public function addImages(Request $request, Property $property)
    {
        $this->gate($property);

        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $hasPrimary = $property->images()->where('is_primary', true)->exists();
        $uploaded = [];

        foreach ($request->file('images') as $i => $image) {
            $path = $image->store("properties/{$property->id}", 'public');
            $isPrimary = ! $hasPrimary && $i === 0;
            $uploaded[] = $property->images()->create([
                'image_path' => $path,
                'is_primary' => $isPrimary,
            ]);
            if ($isPrimary) {
                $hasPrimary = true;
            }
        }

        return response()->json($uploaded, 201);
    }

    public function deleteImage(Property $property, PropertyImage $image)
    {
        $this->gate($property);
        abort_if($image->property_id !== $property->id, 404);

        $wasPrimary = $image->is_primary;
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        if ($wasPrimary) {
            $property->images()->first()?->update(['is_primary' => true]);
        }

        return response()->json(['message' => 'Image deleted.']);
    }

    public function setPrimaryImage(Property $property, PropertyImage $image)
    {
        $this->gate($property);
        abort_if($image->property_id !== $property->id, 404);

        $property->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return response()->json($image);
    }

    private function gate(Property $property): void
    {
        if (! auth()->user()->isSuperAdmin() && $property->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
