<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\CatalogRoute;
use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogIndexRequest;
use App\Http\Requests\StoreCatalogRequest;
use App\Http\Requests\UpdateCatalogRequest;
use App\Http\Resources\CatalogResource;
use App\Http\Resources\UtilityAccountResource;
use App\Models\Payment;
use App\Models\User;
use App\Models\UtilityAccount;
use App\Services\CatalogService;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogApiController extends Controller
{
    public function __construct(private readonly CatalogService $catalog, private readonly PaymentService $payments) {}

    public function index(CatalogIndexRequest $request): AnonymousResourceCollection
    {
        $key = CatalogRoute::key($request->route());
        $this->authorize('viewAny', CatalogRoute::model($key));
        $records = $this->catalog->paginate($request->user(), $key, $request->validated());

        return $key === 'utility-accounts' ? UtilityAccountResource::collection($records) : CatalogResource::collection($records);
    }

    public function show(Request $request, Model $record): JsonResource
    {
        $key = CatalogRoute::key($request->route());
        $record->load(config('catalog.'.$key.'.relations'));
        $this->authorize('view', $record);

        return $this->resource($record);
    }

    public function store(StoreCatalogRequest $request): JsonResponse
    {
        $key = CatalogRoute::key($request->route());
        $record = $this->catalog->create($request->user(), $key, $request->validated());

        return $this->resource($record->refresh()->load(config('catalog.'.$key.'.relations')))
            ->response()->setStatusCode(201);
    }

    public function update(UpdateCatalogRequest $request, Model $record): JsonResource
    {
        $key = CatalogRoute::key($request->route());
        $updated = $this->catalog->update($request->user(), $key, $record, $request->validated());

        return $this->resource($updated->refresh()->load(config('catalog.'.$key.'.relations')));
    }

    public function destroy(Request $request, Model $record): JsonResponse
    {
        $this->catalog->delete($request->user(), CatalogRoute::key($request->route()), $record);

        return response()->json(null, 204);
    }

    public function confirm(Request $request, Payment $payment): CatalogResource
    {
        return new CatalogResource($this->payments->confirm($payment, $request->user()));
    }

    public function cancel(Request $request, Payment $payment): CatalogResource
    {
        return new CatalogResource($this->payments->cancel($payment, $request->user()));
    }

    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', new User);

        return response()->json(['data' => $this->catalog->statistics()]);
    }

    private function resource(Model $item): JsonResource
    {
        return $item instanceof UtilityAccount ? new UtilityAccountResource($item) : new CatalogResource($item);
    }
}
