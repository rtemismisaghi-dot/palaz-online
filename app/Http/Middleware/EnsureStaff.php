<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->session()->boolean('palaz_staff_authenticated')) {
            return redirect()->route('login');
        }

        $role = $request->session()->get('palaz_staff_role');
        if ($roles && !in_array($role, $roles, true)) {
            abort(403, 'دسترسی شما به این بخش مجاز نیست.');
        }

        return $next($request);
    }
}
