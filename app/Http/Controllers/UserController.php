<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['sometimes', 'email']]);
        $query = User::with('utilityAccounts')->orderBy('id');
        if (isset($data['email'])) {
            $query->where('email', $data['email']);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
        ]);

        return response()->json(['data' => User::create($data)->refresh()], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        if (! Auth::attempt($credentials + ['status' => 'active'])) {
            throw ValidationException::withMessages(['email' => 'Перевірте пошту та пароль.']);
        }
        $request->session()->regenerate();

        return response()->json(['data' => $request->user(), 'csrf_token' => $request->session()->token()]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->load('utilityAccounts')]);
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function block(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->status = 'blocked';
        $user->save();

        return response()->json(['data' => $user]);
    }

    public function statistics(): JsonResponse
    {
        return response()->json(['data' => [
            'users' => User::count(),
            'unpaid_bills' => Bill::where('status', 'unpaid')->count(),
            'paid_total' => Payment::where('status', 'paid')->sum('amount'),
        ]]);
    }
}
