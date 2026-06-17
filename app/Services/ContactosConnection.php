<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PendingApiRequest;
use App\Models\Contacto;

class ContactosConnection
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.contactos_app.base_url');
        $this->token = config('services.contactos_app.token');
    }

    protected function client()
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ])->baseUrl($this->baseUrl)->timeout(5);
    }

    public function get($endpoint)
    {
        try {
            $response = $this->client()->get($endpoint);
            if ($response->successful()) {
                $data = $response->json();
                $this->syncLocalCache($data['data'] ?? $data);
                return $data;
            }
            throw new \Exception('API Error: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning('API Contactos unreachable (GET): ' . $e->getMessage());
            // Fallback to local cache array.
            return ['data' => Contacto::all()->toArray(), 'offline' => true];
        }
    }

    public function post($endpoint, $payload)
    {
        try {
            $response = $this->client()->post($endpoint, $payload);
            if ($response->successful()) {
                $data = $response->json();
                $this->syncLocalCache([$data['data'] ?? $data]);
                return $data;
            }
            throw new \Exception('API Error: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning('API Contactos unreachable (POST). Queuing request.');
            $this->queueRequest('POST', $endpoint, $payload);
            Contacto::create($payload);
            return ['status' => 'queued', 'offline' => true, 'message' => 'Guardado localmente. Se sincronizará pronto.'];
        }
    }

    public function put($endpoint, $payload)
    {
        try {
            $response = $this->client()->put($endpoint, $payload);
            if ($response->successful()) {
                $data = $response->json();
                $this->syncLocalCache([$data['data'] ?? $data]);
                return $data;
            }
            throw new \Exception('API Error: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning('API Contactos unreachable (PUT). Queuing request.');
            $this->queueRequest('PUT', $endpoint, $payload);
            if (isset($payload['external_id'])) {
                Contacto::where('external_id', $payload['external_id'])->update($payload);
            }
            return ['status' => 'queued', 'offline' => true, 'message' => 'Actualizado localmente. Se sincronizará pronto.'];
        }
    }

    public function delete($endpoint)
    {
        try {
            $response = $this->client()->delete($endpoint);
            if ($response->successful()) {
                $id = basename($endpoint);
                Contacto::where('external_id', $id)->delete();
                return $response->json();
            }
            throw new \Exception('API Error: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning('API Contactos unreachable (DELETE). Queuing request.');
            $this->queueRequest('DELETE', $endpoint, []);
            $id = basename($endpoint);
            Contacto::where('external_id', $id)->delete();
            return ['status' => 'queued', 'offline' => true, 'message' => 'Eliminado localmente. Se sincronizará pronto.'];
        }
    }

    protected function queueRequest($method, $endpoint, $payload)
    {
        PendingApiRequest::create([
            'method' => $method,
            'endpoint' => $endpoint,
            'payload' => $payload,
        ]);
    }

    protected function syncLocalCache($items)
    {
        if (!is_array($items)) return;
        
        if (isset($items['id']) || isset($items['external_id'])) {
            $items = [$items];
        }

        foreach ($items as $item) {
            if (is_array($item)) {
                $externalId = $item['id'] ?? $item['external_id'] ?? null;
                if ($externalId) {
                    Contacto::updateOrCreate(
                        ['external_id' => $externalId],
                        [
                            'name' => $item['name'] ?? '',
                            'email' => $item['email'] ?? null,
                            'phone' => $item['phone'] ?? null,
                        ]
                    );
                }
            }
        }
    }
}
