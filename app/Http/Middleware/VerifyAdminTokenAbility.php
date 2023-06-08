<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Request as RequestAlias;

class VerifyAdminTokenAbility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $page)
    {
        $user = Auth::user();

        switch ($request->method()) {
            case RequestAlias::METHOD_GET:
                if (!$user->tokenCan($page . ':read')) {
                    abort(403, 'Your account is forbidden to do this manipulation.');
                }
                break;
            case RequestAlias::METHOD_POST:
            case RequestAlias::METHOD_PATCH:
            case RequestAlias::METHOD_DELETE:
            case RequestAlias::METHOD_PUT:
                if (!$user->tokenCan($page . ':update')) {
                    abort(403, 'Your account is forbidden to do this manipulation.');
                }
                break;
        }

        return $next($request);
    }
}
