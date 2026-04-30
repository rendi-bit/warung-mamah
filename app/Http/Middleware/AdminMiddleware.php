<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->role || auth()->user()->role->role_name !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang boleh masuk.');
        }

        return $next($request);
    }
}