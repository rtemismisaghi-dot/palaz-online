<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->boolean('palaz_staff_authenticated')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
