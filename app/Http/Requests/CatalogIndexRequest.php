<?php

namespace App\Http\Requests;

use App\Http\CatalogRoute;
use Illuminate\Foundation\Http\FormRequest;

class CatalogIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return match (CatalogRoute::key($this->route())) {
            'bills' => ['account_id' => 'sometimes|integer|min:1', 'status' => 'sometimes|in:unpaid,paid'],
            'payments' => ['from' => 'sometimes|date_format:Y-m-d', 'to' => $this->has('from')
                ? 'sometimes|date_format:Y-m-d|after_or_equal:from' : 'sometimes|date_format:Y-m-d'],
            'users' => ['email' => 'sometimes|nullable|email'],
            default => [],
        };
    }
}
