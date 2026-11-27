<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Resources\CatalogResource;
use App\Http\Resources\UtilityAccountResource;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use App\Models\UtilityAccount;
use App\Services\CatalogRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogApiController extends Controller
{
    public function __construct(private CatalogRecords $records) {}

    #[OA\Get(path: '/utility-accounts', summary: 'Список записів', security: [[], ['bearerAuth' => []]], tags: ['Особові рахунки'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UtilityAccount')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')]))])]
    #[OA\Get(path: '/service-providers', summary: 'Список записів', tags: ['Постачальники'], parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ServiceProvider')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')]))])]
    #[OA\Get(path: '/utility-services', summary: 'Список записів', tags: ['Послуги й тарифи'], parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UtilityService')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')]))])]
    #[OA\Get(path: '/bills', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Нарахування'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'account_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['unpaid', 'paid'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Bill')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
    #[OA\Get(path: '/meter-readings', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Показники'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MeterReading')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
    #[OA\Get(path: '/payments', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Платежі'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Payment')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
    #[OA\Get(path: '/receipts', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Квитанції'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Receipt')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
    #[OA\Get(path: '/users', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Користувачі'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'email', in: 'query', schema: new OA\Schema(type: 'string', format: 'email')),
        ],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
    public function index(Request $request): AnonymousResourceCollection
    {
        $key = $this->key($request);
        $definition = config('catalog.'.$key);
        $model = new $definition['model'];
        $this->authorize('viewAny', $model);
        $query = $this->isPublic($key) ? $model->newQuery() : $this->records->query($request, $key);
        if ($key === 'bills') {
            $filters = $request->validate(['account_id' => 'sometimes|integer|min:1', 'status' => 'sometimes|in:unpaid,paid']);
            $query->when($filters['account_id'] ?? null, fn ($q, $id) => $q->where('utility_account_id', $id))
                ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
        }
        if ($key === 'payments') {
            $filters = $request->validate(['from' => 'sometimes|date_format:Y-m-d',
                'to' => $request->has('from') ? 'sometimes|date_format:Y-m-d|after_or_equal:from' : 'sometimes|date_format:Y-m-d']);
            $query->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('paid_at', '>=', $date))
                ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('paid_at', '<=', $date));
        }
        if ($key === 'users') {
            $filters = $request->validate(['email' => 'sometimes|email']);
            $query->when($filters['email'] ?? null, fn ($q, $email) => $q->where('email', $email));
        }
        $rows = $query->with($definition['relations'])->latest('id')->paginate(10)->withQueryString();

        return $key === 'utility-accounts' ? UtilityAccountResource::collection($rows) : CatalogResource::collection($rows);
    }

    #[OA\Get(path: '/utility-accounts/{id}', summary: 'Деталі запису', security: [[], ['bearerAuth' => []]], tags: ['Особові рахунки'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityAccount')])), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    #[OA\Get(path: '/service-providers/{id}', summary: 'Деталі запису', tags: ['Постачальники'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProvider')])), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    #[OA\Get(path: '/utility-services/{id}', summary: 'Деталі запису', tags: ['Послуги й тарифи'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityService')])), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    #[OA\Get(path: '/bills/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Нарахування'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Bill')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    #[OA\Get(path: '/meter-readings/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Показники'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MeterReading')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    #[OA\Get(path: '/payments/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Платежі'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    #[OA\Get(path: '/receipts/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Квитанції'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Receipt')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    #[OA\Get(path: '/users/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Користувачі'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    public function show(Request $request, int $id): JsonResource
    {
        $key = $this->key($request);
        $item = $this->find($key, $id);
        $this->authorize('view', $item);

        return $this->resource($item);
    }

    #[OA\Post(path: '/utility-accounts', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Особові рахунки'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UtilityAccountInput', description: 'Поля запису')),
        responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityAccount')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Post(path: '/service-providers', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Постачальники'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ServiceProviderInput', description: 'Поля запису')),
        responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProvider')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Post(path: '/utility-services', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Послуги й тарифи'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UtilityServiceInput', description: 'Поля запису')),
        responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityService')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Post(path: '/bills', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Нарахування'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BillInput', description: 'Поля запису')),
        responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Bill')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Post(path: '/meter-readings', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Показники'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/MeterReadingInput', description: 'Поля запису')),
        responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MeterReading')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Post(path: '/payments', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Платежі'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PaymentInput', description: 'Поля запису')),
        responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Post(path: '/receipts', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Квитанції'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReceiptInput', description: 'Поля запису')),
        responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Receipt')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Post(path: '/users', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Користувачі'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserInput', description: 'Поля запису')),
        responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    public function store(Request $request): JsonResponse
    {
        $key = $this->key($request);
        $model = config('catalog.'.$key.'.model');
        $item = new $model;
        $this->authorize('create', $item);
        $item = $this->records->save($request, $key, $item);

        return $this->resource($item->refresh()->load(config('catalog.'.$key.'.relations')))
            ->response()->setStatusCode(201);
    }

    #[OA\Put(path: '/utility-accounts/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Особові рахунки'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UtilityAccountInput', description: 'Поля запису')),
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityAccount')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Put(path: '/service-providers/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Постачальники'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ServiceProviderInput', description: 'Поля запису')),
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProvider')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Put(path: '/utility-services/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Послуги й тарифи'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UtilityServiceInput', description: 'Поля запису')),
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityService')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Put(path: '/bills/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Нарахування'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BillInput', description: 'Поля запису')),
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Bill')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Put(path: '/meter-readings/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Показники'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/MeterReadingInput', description: 'Поля запису')),
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MeterReading')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Put(path: '/payments/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Платежі'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PaymentInput', description: 'Поля запису')),
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Put(path: '/receipts/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Квитанції'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReceiptInput', description: 'Поля запису')),
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Receipt')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Put(path: '/users/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Користувачі'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserUpdateInput')),
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    public function update(Request $request, int $id): JsonResource
    {
        $key = $this->key($request);
        $item = $this->find($key, $id);
        $this->authorize('update', $item);
        $item = $this->records->save($request, $key, $item);

        return $this->resource($item->refresh()->load(config('catalog.'.$key.'.relations')));
    }

    #[OA\Delete(path: '/utility-accounts/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Особові рахунки'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Delete(path: '/service-providers/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Постачальники'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Delete(path: '/utility-services/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Послуги й тарифи'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Delete(path: '/bills/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Нарахування'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Delete(path: '/meter-readings/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Показники'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Delete(path: '/payments/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Платежі'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Delete(path: '/receipts/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Квитанції'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    #[OA\Delete(path: '/users/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Користувачі'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $key = $this->key($request);
        $this->authorize('delete', $this->find($key, $id));
        $this->records->delete($request, $key, $id);

        return response()->json(null, 204);
    }

    #[OA\Post(path: '/payments/{id}/confirm', summary: 'Підтвердити навчальний платіж', security: [['bearerAuth' => []]], tags: ['Платежі'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Платіж', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Чужий платіж'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 409, description: 'Несумісний стан'), new OA\Response(response: 422, description: 'Недостатній залишок')])]
    public function confirm(int $id, PaymentController $payments): CatalogResource
    {
        $payment = $this->find('payments', $id);
        $this->authorize('view', $payment);
        $payments->confirm($id);

        return new CatalogResource($payment->refresh());
    }

    #[OA\Post(path: '/payments/{id}/cancel', summary: 'Скасувати навчальний платіж', security: [['bearerAuth' => []]], tags: ['Платежі'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Платіж', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Чужий платіж'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 409, description: 'Несумісний стан')])]
    public function cancel(int $id, PaymentController $payments): CatalogResource
    {
        $payment = $this->find('payments', $id);
        $this->authorize('view', $payment);
        $payments->cancel($id);

        return new CatalogResource($payment->refresh());
    }

    #[OA\Get(path: '/receipts/{id}/download', summary: 'Завантажити квитанцію', security: [['bearerAuth' => []]], tags: ['Квитанції'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Текстовий файл', content: new OA\MediaType(mediaType: 'text/plain', schema: new OA\Schema(type: 'string'))), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Чужа квитанція'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
    public function download(int $id, ReceiptController $receipts): StreamedResponse
    {
        $this->authorize('view', $this->find('receipts', $id));

        return $receipts->download($id);
    }

    #[OA\Get(path: '/statistics', summary: 'Зведені показники для адміністратора', security: [['bearerAuth' => []]], tags: ['Користувачі'],
        responses: [new OA\Response(response: 200, description: 'Статистика', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', properties: [new OA\Property(property: 'users', type: 'integer'), new OA\Property(property: 'unpaid_bills', type: 'integer'), new OA\Property(property: 'paid', type: 'number')], type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Лише адміністратор')])]
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', new User);

        return response()->json(['data' => [
            'users' => User::count(),
            'unpaid_bills' => Bill::where('status', 'unpaid')->count(),
            'paid' => (float) Payment::where('status', 'paid')->sum('amount'),
        ]]);
    }

    private function key(Request $request): string
    {
        return explode('.', $request->route()->getName())[2];
    }

    private function isPublic(string $key): bool
    {
        return in_array($key, ['utility-accounts', 'service-providers', 'utility-services'], true);
    }

    private function find(string $key, int $id): Model
    {
        $definition = config('catalog.'.$key);

        return $definition['model']::with($definition['relations'])->findOrFail($id);
    }

    private function resource(Model $item): JsonResource
    {
        return $item instanceof UtilityAccount ? new UtilityAccountResource($item) : new CatalogResource($item);
    }
}
