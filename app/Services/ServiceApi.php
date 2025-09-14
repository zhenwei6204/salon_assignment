<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Service;

class ServiceApi
{
    private string $base;

    public function __construct()
    {
        $host = request()?->getSchemeAndHttpHost() ?: config('app.url', 'http://127.0.0.1:8000');
        $this->base = rtrim(env('SERVICE_API_BASE', $host . '/api/v1'), '/');
    }

    private function isSameApp(): bool
    {
        $current = request()?->getSchemeAndHttpHost();
        return str_starts_with($this->base, rtrim($current, '/') . '/');
    }

    private function http()
    {
        return Http::acceptJson()->timeout(10);
    }

    public function listServices(string $search = '', int $page = 1): array
    {
        // Fallback to DB if base == current host (avoids deadlock on php dev server)
        if ($this->isSameApp()) {
            $q = Service::query()->select('id', 'name', 'active');
            if ($search !== '') {
                $q->where('name', 'like', "%{$search}%");
            }
            $items = $q->orderBy('name')->limit(50)->get()
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'active' => (bool) $s->active])
                ->toArray();

            return ['data' => $items, 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 50, 'total' => count($items)]];
        }

        // Real HTTP call when pointing to another service / port
        $params = ['page' => $page];
        if ($search !== '') $params['search'] = $search;

        return $this->http()->get("{$this->base}/services", $params)->throw()->json();
    }

    public function getService(int $id): ?array
    {
        if ($this->isSameApp()) {
            $s = Service::find($id);
            return $s ? ['id' => $s->id, 'name' => $s->name, 'active' => (bool) $s->active] : null;
        }

        return $this->http()->get("{$this->base}/services/{$id}")->throw()->json();
    }
}
