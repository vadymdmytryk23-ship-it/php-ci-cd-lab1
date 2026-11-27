<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UtilityAccountResource extends JsonResource
{
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->headers->set('Cache-Control', 'private, no-store');
    }

    public function toArray(Request $request): array
    {
        $private = $request->user('sanctum')?->can('viewPrivate', $this->resource) === true;

        return [
            'id' => $this->id,
            'account_number' => $this->when($private, $this->account_number),
            'address' => $this->when($private, $this->address),
            'owner_id' => $this->when($private, $this->user_id),
            'status' => $this->when($private, $this->status),
            'services' => $this->whenLoaded('utilityServices', fn () => $this->utilityServices->map(fn ($service) => [
                'id' => $service->id, 'name' => $service->name,
            ])),
            'created_at' => $this->when($private, fn () => $this->created_at?->toISOString()),
        ];
    }
}
