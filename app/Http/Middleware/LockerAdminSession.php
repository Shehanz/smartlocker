<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LockerAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('locker_admin_authenticated') !== true) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi admin berakhir. Silakan login kembali.'], 401);
            }

            return redirect()->route('locker.login');
        }

        return $next($request);
    }
}
