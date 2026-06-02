<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\VisitorSession;
use App\Models\VisitorAction;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    public function show($slug)
    {
        $catalog = Catalog::where('slug', $slug)->firstOrFail();

        if (!$catalog->published) {
            return response()->json(['expired' => true, 'message' => 'Este catálogo no está disponible.'], 404);
        }

        if ($catalog->starts_at && now()->lt($catalog->starts_at)) {
            return response()->json([
                'scheduled' => true,
                'message' => 'Este catálogo estará disponible desde el ' . $catalog->starts_at->format('d/m/Y') . ' hasta el ' . ($catalog->ends_at ? $catalog->ends_at->format('d/m/Y') : 'indefinido') . '.',
            ], 403);
        }

        if ($catalog->ends_at && now()->gt($catalog->ends_at)) {
            return response()->json(['expired' => true, 'message' => 'Este catálogo venció el ' . $catalog->ends_at->format('d/m/Y') . '.'], 410);
        }

        return response()->json([
            'id'          => $catalog->id,
            'name'        => $catalog->name,
            'logo_url'    => $catalog->logo_url,
            'brand_name'  => $catalog->brand_name,
            'description' => $catalog->description,
            'slug'        => $catalog->slug,
            'owner'       => ['whatsapp_number' => $catalog->user->whatsapp_number],
            'products'    => $catalog->products()->where('active', true)->orderBy('catalog_products.order')->get(),
        ]);
    }

    public function register(Request $request, $slug)
    {
        $catalog = Catalog::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:20',
            'fingerprint' => 'nullable|string',
        ]);

        $session = VisitorSession::firstOrCreate(
            ['catalog_id' => $catalog->id, 'phone' => $data['phone']],
            [
                'name'        => $data['name'],
                'fingerprint' => $data['fingerprint'] ?? null,
                'ip'          => $request->ip(),
            ]
        );

        return response()->json(['session_id' => $session->id], 201);
    }

    public function actions(Request $request, $slug)
    {
        $request->validate(['session_id' => 'required|integer|exists:visitor_sessions,id']);
        $actions = VisitorAction::where('visitor_session_id', $request->session_id)->get(['product_id', 'action']);
        return response()->json($actions);
    }

    public function track(Request $request, $slug)
    {
        $data = $request->validate([
            'session_id' => 'required|integer|exists:visitor_sessions,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'action'     => 'required|in:like,archive,whatsapp,view',
        ]);

        $existing = VisitorAction::where('visitor_session_id', $data['session_id'])
            ->where('product_id', $data['product_id'] ?? null)
            ->where('action', $data['action'])
            ->first();

        if ($existing && in_array($data['action'], ['like', 'archive'])) {
            $existing->delete();
            return response()->json(['ok' => true, 'toggled' => false]);
        }

        if (!$existing) {
            VisitorAction::create([
                'visitor_session_id' => $data['session_id'],
                'product_id'         => $data['product_id'] ?? null,
                'action'             => $data['action'],
            ]);
        }

        return response()->json(['ok' => true, 'toggled' => true]);
    }
}
