<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class StoreIntendedUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() && !$request->is('login', 'register')) {
            Session::put('url.intended', $request->fullUrl());
        }

        return $next($request);
    }
}
