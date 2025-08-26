<?php

namespace App\Http\Middleware;

use App\Enums\Roles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminMiddleware
 *
 * @package App\Http\Middleware
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guest()) {
            abort(401, __('Unauthenticated'));
        }

        if (!auth()->user()->hasRole(Roles::ADMIN)) {
            abort(response()->json([
                'success' => false,
                'message' => __('This action is unauthorized.'),
            ], 403));
        }

        return $next($request);
    }
}
