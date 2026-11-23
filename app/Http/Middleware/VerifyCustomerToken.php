<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCustomerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post') && $request->header('X-Customer-Token') !== 'customer-15') {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Відхилено: відсутній або неправильний заголовок X-Customer-Token.',
            ], 403);
        }

        $response = $next($request);
        $response->headers->set('X-Security-Policy', 'Enforced');

        return $response;
    }
}
