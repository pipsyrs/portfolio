<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dashboard ini single-account. Middleware `auth` hanya membuktikan ada sesi;
 * lapis ini membuktikan sesi tersebut milik pemilik portfolio.
 */
class EnsureIsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isOwner(), 403);

        return $next($request);
    }
}
