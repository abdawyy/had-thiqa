<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login', ['as' => $roles[0] ?? 'worker']);
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()->route(match ($user->role) {
                'seller' => 'seller.home',
                'admin' => 'admin.home',
                default => 'worker.home',
            });
        }

        return $next($request);
    }
}
