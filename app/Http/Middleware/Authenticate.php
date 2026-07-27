<?php

namespace App\Http\Middleware;


use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\RedirectResponse;

class Authenticate extends Middleware
{

    protected function redirectTo(\Illuminate\Http\Request $request): ?RedirectResponse
    {
        if (! $request->expectsJson()) {
            return to_route('login');
        }
        return null;
    }
}
