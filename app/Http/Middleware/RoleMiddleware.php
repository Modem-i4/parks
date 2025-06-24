<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;


class RoleMiddleware
{
   public function handle(Request $request, Closure $next, string ...$params): Response
    {
        $user = $request->user();

        if (! $user) abort(403);

        [$mode, $roleValue] = count($params) === 2 ? $params : ['atLeast', $params[0]]; 

        
        $role = UserRole::fromString($roleValue);

        if ($mode === 'isOnly' && ! $user->isOnly($role)) {
            abort(403);
        }

        if ($mode === 'atLeast' && ! $user->atLeast($role)) {
            abort(403);
        }

        return $next($request);
    }
}
