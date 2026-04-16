<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // HARDCODE UNTUK TESTING DARI USER
        session([
            'UserID' => 1,
            'username' => 'admin',
            'LevelUser' => '20'
        ]);

        if (!session()->has('username')) {
            return redirect()->route('welcome')->with('error', 'Silahkan login terlebih dahulu.');
        }

        return $next($request);
    }
}
