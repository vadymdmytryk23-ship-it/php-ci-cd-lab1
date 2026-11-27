<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveTokenUser
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->status === 'active', 403, 'Обліковий запис заблоковано.');

        return $next($request);
    }
}
