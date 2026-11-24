<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceProviderController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => ServiceProvider::with('utilityServices')->orderBy('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:service_providers'],
            'edrpou' => ['required', 'digits:8', 'unique:service_providers'],
            'bank_account' => ['required', 'string', 'max:34'],
            'contact_email' => ['required', 'email', 'max:255'],
        ]);

        return response()->json(['data' => ServiceProvider::create($data)], 201);
    }
}
