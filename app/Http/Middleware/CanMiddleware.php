<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserAbilities;

class CanMiddleware
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = $request->user();
        if (! $user) abort(403);

        $abilityEnum = UserAbilities::from($ability);
        $minRole = $abilityEnum->minRole();

        if (! $user->atLeast($minRole)) abort(403);

        return $next($request);
    }
}
