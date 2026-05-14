<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XSS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $userInput = $request->all();
        array_walk_recursive($userInput, function (&$userInput, $key) {
            if (is_string($userInput) && !in_array($key, ['content', 'email_content'])) {
                $userInput = strip_tags($userInput);
            }
        });
        $request->merge($userInput);

        return $next($request);
    }
}
