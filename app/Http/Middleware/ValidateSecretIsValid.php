<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateSecretIsValid
{
    public function handle(Request $request, Closure $next)
    {
        $headers = $request->header();

        if (!isset($headers['secret']) || $headers['secret'][0] != env('APP_SECRET')) {
            abort(403, "Sem acesso.");
        }

        return $next($request);
    }
}
