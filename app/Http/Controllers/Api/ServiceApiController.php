<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceApiController extends Controller
{
   public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) ($request->integer('per_page') ?: 25);

        $p = Service::query()
            ->select('id', 'name', 'is_available')
            ->when($search !== '', fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'data' => $p->getCollection()
                ->map(fn($s) => [
                    'id' => (int) $s->id,
                    'name' => (string) $s->name,
                    'active' => (bool) ($s->is_available ?? 0),
                ])
                ->values(),
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page' => $p->lastPage(),
                'per_page' => $p->perPage(),
                'total' => $p->total(),
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
