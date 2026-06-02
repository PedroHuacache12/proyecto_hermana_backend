<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->products()->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'brand'       => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price'             => 'nullable|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0',
            'benefits'    => 'nullable|array',
            'benefits.*'  => 'string',
            'preparation' => 'nullable|array',
            'images'           => 'nullable|array',
            'images.*'         => 'string',
            'background_image' => 'nullable|string',
            'ingredients'   => 'nullable|array',
            'ingredients.*.name' => 'string',
            'ingredients.*.icon' => 'nullable|string',
            'active'        => 'boolean',
        ]);

        $product = $request->user()->products()->create($data);
        return response()->json($product, 201);
    }

    public function show(Request $request, Product $product)
    {
        $this->authorize($request->user(), $product);
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize($request->user(), $product);

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'brand'       => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price'             => 'nullable|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0',
            'benefits'    => 'nullable|array',
            'benefits.*'  => 'string',
            'preparation' => 'nullable|array',
            'images'           => 'nullable|array',
            'images.*'         => 'string',
            'background_image' => 'nullable|string',
            'ingredients'   => 'nullable|array',
            'ingredients.*.name' => 'string',
            'ingredients.*.icon' => 'nullable|string',
            'active'        => 'boolean',
        ]);

        $product->update($data);
        return response()->json($product);
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorize($request->user(), $product);
        $product->delete();
        return response()->json(null, 204);
    }

    public function uploadImage(Request $request)
    {
        $request->validate(['image' => 'required|image|max:5120']);

        $file = $request->file('image');
        $image = ImageManager::gd()->read($file->getContent())->scaleDown(width: 1200);
        $filename = 'products/' . uniqid() . '.jpg';
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
        Storage::disk($disk)->put($filename, $image->toJpeg(85));

        return response()->json(['url' => Storage::disk($disk)->url($filename)]);
    }

    private function authorize($user, Product $product)
    {
        if ($product->user_id !== $user->id) {
            abort(403);
        }
    }
}
