<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()->hasRole($role)) {
            return \response()->json(['message' => 'You are not authorized to access this resource.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
