<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PendingApiRequest;
use Illuminate\Support\Facades\Http;

class SyncPendingApiRequests extends Command
{
    protected $signature = 'contactos:sync-pending';
    protected $description = 'Sincroniza las peticiones pendientes con la API de contactos_app';

    public function handle()
    {
        $pendingRequests = PendingApiRequest::orderBy('created_at', 'asc')->get();
        $baseUrl = config('services.contactos_app.base_url');
        $token = config('services.contactos_app.token');

        if ($pendingRequests->isEmpty()) {
            $this->info('No hay peticiones pendientes.');
            return;
        }

        $client = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->baseUrl($baseUrl)->timeout(5);

        foreach ($pendingRequests as $request) {
            $this->info("Intentando sync: {$request->method} {$request->endpoint}");
            
            try {
                $response = null;
                switch (strtoupper($request->method)) {
                    case 'POST':
                        $response = $client->post($request->endpoint, $request->payload);
                        break;
                    case 'PUT':
                        $response = $client->put($request->endpoint, $request->payload);
                        break;
                    case 'DELETE':
                        $response = $client->delete($request->endpoint);
                        break;
                }

                if ($response && $response->successful()) {
                    $this->info("Éxito: {$request->id}");
                    $request->delete();
                } else {
                    $error = $response ? $response->status() : 'No response';
                    $this->error("Fallo API status: {$error}");
                    $request->update([
                        'attempts' => $request->attempts + 1,
                        'last_error' => 'API HTTP Status: ' . $error
                    ]);
                }
            } catch (\Exception $e) {
                $this->error("Error de conexión: " . $e->getMessage());
                $request->update([
                    'attempts' => $request->attempts + 1,
                    'last_error' => $e->getMessage()
                ]);
            }
        }
    }
}
