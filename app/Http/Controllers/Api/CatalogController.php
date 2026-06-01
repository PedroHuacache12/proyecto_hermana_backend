<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->catalogs()->withCount('visitorSessions')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'description'=> 'nullable|string',
            'logo_url'   => 'nullable|string',
            'brand_name' => 'nullable|string|max:100',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);

        $catalog = $request->user()->catalogs()->create($data);
        return response()->json($catalog, 201);
    }

    public function show(Request $request, Catalog $catalog)
    {
        $this->gate($request->user(), $catalog);
        return response()->json($catalog->load('products'));
    }

    public function update(Request $request, Catalog $catalog)
    {
        $this->gate($request->user(), $catalog);

        $data = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'description'=> 'nullable|string',
            'logo_url'   => 'nullable|string',
            'brand_name' => 'nullable|string|max:100',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date',
            'published'  => 'boolean',
        ]);

        $catalog->update($data);
        return response()->json($catalog);
    }

    public function destroy(Request $request, Catalog $catalog)
    {
        $this->gate($request->user(), $catalog);
        $catalog->delete();
        return response()->json(null, 204);
    }

    public function syncProducts(Request $request, Catalog $catalog)
    {
        $this->gate($request->user(), $catalog);
        $request->validate(['product_ids' => 'required|array', 'product_ids.*' => 'integer']);

        $sync = collect($request->product_ids)->mapWithKeys(fn($id, $i) => [$id => ['order' => $i]]);
        $catalog->products()->sync($sync);

        return response()->json($catalog->load('products'));
    }

    public function publish(Request $request, Catalog $catalog)
    {
        $this->gate($request->user(), $catalog);
        $catalog->update(['published' => !$catalog->published]);
        return response()->json($catalog);
    }

    private function gate($user, Catalog $catalog)
    {
        if ($catalog->user_id !== $user->id) abort(403);
    }
}
