<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditTrails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        AuditTrails::create([
            'user_id' => $user->id,
            'action' => 'register',
            'resource' => 'user',
            'details' => 'User registered: ' . $user->email,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        AuditTrails::create([
            'user_id' => $user->id,
            'action' => 'login',
            'resource' => 'user',
            'details' => 'User logged in: ' . $user->email,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        AuditTrails::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'resource' => 'user',
            'details' => 'User logged out: ' . $user->email,
        ]);
        
        $user->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}
