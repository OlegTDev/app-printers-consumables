<?php

namespace App\Http\Middleware;

use App\Models\Auth\LdapAuthenticate;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Аутентификация через сервер LDAP
        $authMethod = env('AUTH_METHOD');
        if (strtoupper($authMethod) === 'LDAP') {
            (new LdapAuthenticate())->login($request);            
            redirect()->intended(RouteServiceProvider::HOME);            
        }

        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
