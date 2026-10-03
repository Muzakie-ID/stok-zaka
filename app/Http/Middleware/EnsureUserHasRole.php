<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pakai sebagai: ->middleware('role:admin')
     * atau     : ->middleware('role:admin,karyawan')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($roles === [] || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Kamu tidak punya akses ke halaman ini.');
    }
}
