<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomLoginController extends Controller
{
    public function create()
    {
        // This will be handled by modal, but keep for fallback
        return view('pages.homepage');
    }

    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Debug: Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            // User doesn't exist
            return response()->json([
                'success' => false,
                'message' => 'User not found with this email.'
            ], 422);
        }

        // FIXED: Use Hash::check() instead of hash_equals()
        // hash_equals() is for comparing strings, not for password hashes
        if (!Hash::check($request->password, $user->password)) {
            // Password doesn't match
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect.'
            ], 422);
        }

        // Attempt to authenticate
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

             $user = Auth::user();

               if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
        }
 
    // Role-based redirect
  
    return redirect()->route('home');

    }

    public function logout(Request $request)
    {
        // REMOVE the dd() - it's stopping execution
        // dd() shows data and stops, so logout never happens
        
        // Just do the logout
 
        Auth::logout(); // Log out the user using the 'web' guard
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}