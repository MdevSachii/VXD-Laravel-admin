<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureWordPressAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('wp_access_token')) {
            return redirect()->route('wp.login');
        }
        return $next($request);
    }
}
