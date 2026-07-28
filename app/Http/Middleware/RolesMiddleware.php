<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $roles = \array_slice(\func_get_args(), 2);

        $user = $request->user();
        if ($user === null) {
            return to_route('login');
        }
        foreach($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }
        abort(403);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

}
