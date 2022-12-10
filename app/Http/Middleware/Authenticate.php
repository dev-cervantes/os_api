<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            return parent::handle($request, $next, ...$guards);
        } catch (AuthenticationException $e) {
            abort(401, "Não autenticado.");
        }
    }

    protected function unauthenticated($request, array $guards)
    {
        throw new AuthenticationException("Unauthenticated.", $guards);
    }
}
