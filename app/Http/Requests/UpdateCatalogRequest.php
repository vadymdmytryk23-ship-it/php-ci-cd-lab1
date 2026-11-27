<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Database\Eloquent\Model;

class UpdateCatalogRequest extends StoreCatalogRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->record());
    }

    protected function record(): Model
    {
        return $this->route('record');
    }
}
