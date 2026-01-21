<?php
namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class CustomRegisterController extends Controller
{
    public function create()
    {
        // This will be handled by modal, but keep for fallback
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
             
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => "client",
            'is_admin'=> false
            
        ]);

        event(new Registered($user));


        // Return JSON response for AJAX or redirect for normal form submission
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'redirect' => route('home')
            ]);
        }

        return redirect()->route('home');
    }
}