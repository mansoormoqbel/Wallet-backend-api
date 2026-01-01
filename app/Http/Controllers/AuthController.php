<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('token')->plainTextToken;
        return response()->json(['token' => $token]);
    }
    public function login(Request $request) {
        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json(['error'=>'Unauthorized'],401);
        }
        $token = Auth::user()->createToken('token')->plainTextToken;
        return response()->json(['token'=>$token]);
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out from current device'
        ]);
    }
}
