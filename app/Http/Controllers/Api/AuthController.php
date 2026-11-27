<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatalogResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(path: '/register', summary: 'Створити профіль споживача й видати токен', tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['name', 'email', 'password', 'password_confirmation'], properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'email', type: 'string', format: 'email'),
            new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
            new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
        ])),
        responses: [new OA\Response(response: 201, description: 'Профіль і токен', content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')), new OA\Response(response: 422, description: 'Помилка валідації')])]
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:72|confirmed',
        ]);

        return $this->token(User::create($data), 201);
    }

    #[OA\Post(path: '/login', summary: 'Отримати Bearer-токен', tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['email', 'password'], properties: [
            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'customer@example.test'),
            new OA\Property(property: 'password', type: 'string', format: 'password'),
        ])),
        responses: [new OA\Response(response: 200, description: 'Профіль і токен', content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')), new OA\Response(response: 422, description: 'Помилка облікових даних')])]
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'password' => 'required|string|max:72']);
        $user = User::where('email', $data['email'])->where('status', 'active')->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Перевірте електронну пошту та пароль.']);
        }

        return $this->token($user);
    }

    #[OA\Post(path: '/logout', summary: 'Відкликати поточний токен', security: [['bearerAuth' => []]], tags: ['Auth'],
        responses: [new OA\Response(response: 204, description: 'Токен відкликано'), new OA\Response(response: 401, description: 'Потрібен токен')])]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    #[OA\Get(path: '/profile', summary: 'Власний профіль', security: [['bearerAuth' => []]], tags: ['Auth'],
        responses: [new OA\Response(response: 200, description: 'Профіль', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])), new OA\Response(response: 401, description: 'Потрібен токен')])]
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
