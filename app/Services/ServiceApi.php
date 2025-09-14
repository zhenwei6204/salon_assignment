<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\RequestException;

class ServiceApi
{
    private string $base = 'http://service-module.test/api/v1'; // <- teammate’s base
    private ?string $token = null;     // set if they use Bearer
    private ?string $staticKey = null; // set if they use X-Service-Key

    private function client()
    {
        $c = Http::acceptJson()->timeout(10);
        if ($this->token) $c = $c->withToken($this->token);
        if ($this->staticKey) $c = $c->withHeaders(['X-Service-Key' => $this->staticKey]);
        return $c;
    }

    public function listServices(string $search = '', int $page = 1): array
    {
        return Cache::remember("svc:list:$search:$page", 300, function () use ($search, $page) {
            $res = $this->client()->get($this->base.'/services', ['search'=>$search,'page'=>$page])->throw();
            return $res->json(); // e.g. ['data'=>[...]] or [...]
        });
    }

    public function getService(int $id): ?array
    {
        return Cache::remember("svc:get:$id", 600, function () use ($id) {
            try { return $this->client()->get($this->base."/services/$id")->throw()->json(); }
            catch (RequestException) { return null; }
        });
    }
}
