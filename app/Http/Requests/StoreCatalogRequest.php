<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\CatalogRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', $this->record());
    }

    public function rules(): array
    {
        $key = CatalogRoute::key($this->route());
        $item = $this->record();
        $rules = [];
        foreach (config('catalog.'.$key.'.fields') as $name => $field) {
            $rules[$name] = explode('|', $field['rules']);
            if ($field['options'] === 'unique') {
                $rules[$name][] = Rule::unique($item->getTable(), $name)->ignore($item->getKey());
            }
        }
        if ($key === 'utility-accounts') {
            $rules['service_ids.*'] = ['integer', 'distinct', 'exists:utility_services,id'];
            $rules['user_id'] = ['prohibited'];
        }
        if ($key === 'users' && $item->exists) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'max:72'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => 'Заповніть це поле.',
            'unique' => 'Такий запис уже існує.',
            'exists' => 'Оберіть дійсне значення.',
            'min.string' => 'Введіть щонайменше :min символів.',
            'numeric' => 'Введіть число.',
            'gte' => 'Значення менше за попередній показник.',
        ];
    }

    protected function record(): Model
    {
        return CatalogRoute::model(CatalogRoute::key($this->route()));
    }
}
