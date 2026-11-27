<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\CatalogResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        return $this->token(User::create($data), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->where('status', 'active')->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Перевірте електронну пошту та пароль.']);
        }

        return $this->token($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    public function profile(Request $request): CatalogResource
    {
        return new CatalogResource($request->user());
    }

    private function token(User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'user' => new CatalogResource($user),
            'token' => $user->createToken('api-token')->plainTextToken,
        ], $status)->header('Cache-Control', 'no-store');
    }
}
