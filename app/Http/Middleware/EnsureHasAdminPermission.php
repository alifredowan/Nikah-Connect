<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isModerator() || ! $user->hasPermission($permission)) {
            abort(403, "Access denied. You do not have the required permission ({$permission}) to access this resource.");
        }

        return $next($request);
    }
}
