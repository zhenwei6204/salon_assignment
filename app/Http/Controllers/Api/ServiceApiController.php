<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceApiController extends Controller
{
    public function index(Request $request)
    {
        $q = Service::query()->select('id', 'name', 'is_available');

        if ($search = trim($request->query('search', ''))) {
            $q->where('name', 'like', "%{$search}%");
        }

        $perPage = min(50, (int) $request->query('per_page', 20));
        $page    = (int) $request->query('page', 1);

        $p = $q->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        $data = collect($p->items())->map(fn ($s) => [
            'id'     => $s['id'],
            'name'   => $s['name'],
            'active' => (bool) ($s['is_available'] ?? 0),
        ])->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'per_page'     => $p->perPage(),
                'total'        => $p->total(),
            ],
        ]);
    }

    public function show(Service $service)
    {
        return response()->json([
            'id'     => $service->id,
            'name'   => $service->name,
            'active' => (bool) ($service->is_available ?? 0),
        ]);
    }
}
