<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\VisitorAction;
use App\Models\VisitorSession;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $catalogIds = $user->catalogs()->pluck('id');
        $sessionIds = VisitorSession::whereIn('catalog_id', $catalogIds)->pluck('id');

        return response()->json([
            'total_visits'   => $sessionIds->count(),
            'total_likes'    => VisitorAction::whereIn('visitor_session_id', $sessionIds)->where('action', 'like')->count(),
            'total_whatsapp' => VisitorAction::whereIn('visitor_session_id', $sessionIds)->where('action', 'whatsapp')->count(),
            'total_catalogs' => $catalogIds->count(),
        ]);
    }

    public function catalog(Request $request, Catalog $catalog)
    {
        if ($catalog->user_id !== $request->user()->id) abort(403);

        $sessionIds = $catalog->visitorSessions()->pluck('id');

        $productStats = VisitorAction::whereIn('visitor_session_id', $sessionIds)
            ->whereNotNull('product_id')
            ->selectRaw('product_id, action, count(*) as total')
            ->groupBy('product_id', 'action')
            ->with('product:id,name,brand,images')
            ->get()
            ->groupBy('product_id')
            ->map(fn($actions) => [
                'product' => $actions->first()->product,
                'likes'     => $actions->where('action', 'like')->sum('total'),
                'whatsapp'  => $actions->where('action', 'whatsapp')->sum('total'),
                'archives'  => $actions->where('action', 'archive')->sum('total'),
            ])->values();

        $visitors = $catalog->visitorSessions()->latest()->get(['id', 'name', 'phone', 'created_at']);

        return response()->json([
            'catalog'       => $catalog->only('id', 'name', 'slug', 'published'),
            'total_visits'  => $visitors->count(),
            'product_stats' => $productStats,
            'visitors'      => $visitors,
        ]);
    }

    public function catalogs(Request $request)
    {
        $catalogs = $request->user()->catalogs()
            ->withCount('visitorSessions')
            ->latest()->get(['id', 'name', 'slug', 'published', 'ends_at']);
        return response()->json($catalogs);
    }
}
