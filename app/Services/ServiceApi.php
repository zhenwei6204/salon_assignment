<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Service;

class ServiceApi
{
    private string $base;
    private bool $forceHttp;

    public function __construct()
    {
        $host = request()?->getSchemeAndHttpHost() ?: config('app.url', 'http://127.0.0.1:8000');
        $this->base = rtrim(env('SERVICE_API_BASE', $host . '/api/v1'), '/');
        $this->forceHttp = filter_var(env('SERVICE_API_FORCE_HTTP', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function isSameApp(): bool
    {
        if ($this->forceHttp) {
            return false;
        }

        $current = rtrim(request()?->getSchemeAndHttpHost() ?? '', '/');

        $p = parse_url($this->base);
        $baseOrigin = ($p['scheme'] ?? 'http') . '://' . ($p['host'] ?? '127.0.0.1') . (isset($p['port']) ? ':' . $p['port'] : '');
        $baseOrigin = rtrim($baseOrigin, '/');

        return $current !== '' && $current === $baseOrigin;
    }

    private function http()
    {
        return Http::acceptJson()->timeout(10);
    }

    public function listServices(string $search = '', int $page = 1): array
    {
        // Force HTTP when the flag is set or origins differ
        if (!$this->isSameApp()) {
            $res = $this->http()
                ->get("{$this->base}/services", ['search' => $search, 'page' => $page])
                ->throw()
                ->json();

            return is_array($res) ? $res : ['data' => [], 'meta' => []];
        }

        // (optional local fallback — safe to leave)
        $q = Service::query()->select('id', 'name', 'is_available');
        if ($search !== '') {
            $q->where('name', 'like', "%{$search}%");
        }
        $items = $q->orderBy('name')->limit(50)->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'active' => (bool) ($s->is_available ?? 0)])
            ->toArray();

        return [
            'data' => $items,
            'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 50, 'total' => count($items)],
        ];
    }

    public function getService(int $id): ?array
    {
        if (!$this->isSameApp()) {
            $res = $this->http()
                ->get("{$this->base}/services/{$id}")
                ->throw()
                ->json();

            return is_array($res) ? $res : null;
        }
    }
}


