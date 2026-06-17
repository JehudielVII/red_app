<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['external_id'] ?? $this->resource['id'] ?? null,
            'name' => $this->resource['name'] ?? null,
            'email' => $this->resource['email'] ?? null,
            'phone' => $this->resource['phone'] ?? null,
            '_offline' => $this->resource['offline'] ?? false,
        ];
    }
}
