<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
   
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login first.');
        }

        $user = Auth::user();
        
        // Simple admin check - adjust based on your User model
        if (!$user || (!$user->is_admin && $user->role !== 'admin')) {
            return redirect()->route('home');
        }
     
       

        return $next($request);
    }

}
