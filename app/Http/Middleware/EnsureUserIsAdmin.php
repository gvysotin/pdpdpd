<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        // Данный middleware не используется, его можно удалить.
        // Вместо него используется Gate в AuthServiceProvider
        // Данный middleware зарегистрирован в bootstrap/app.php
        // На некущий момент он закомментирован там.
        // Код ниже оставил для понимания работы middleware.


        // if(!auth()->user()->is_admin) {
        //     abort(403, 'Forbidden by EnsureUserIsAdmin');
        // }
        //return $next($request);





        Log::info('before code execution');
        $response = $next($request);
        Log::info('after code execution');
        return $response;
    }
}
