<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){

        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $validatedData;

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credentials do not match'], 401);
        }

        $user = User::where('email', $request->email)->first();

        $token = $user->createToken('Api Token for ' . $user->email, [])->plainTextToken;
           
        if($user->user_type === 'patient'){
            $fullName = $user->patient->first_name . ($user->patient->middle_name ? ' ' . $user->patient->middle_name : '') . ' ' . $user->patient->last_name . ($user->patient->extension_name ? ' ' . $user->patient->extension_name : '');
        }else if($user->user_type === 'admin'){
            $fullName = $user->admin->first_name . ($user->admin->middle_name ? ' ' . $user->admin->middle_name : '') . ' ' . $user->admin->last_name . ($user->admin->extension_name ? ' ' . $user->admin->extension_name : '');
        }

        
        
        return response()->json([
            'token' => $token,
            'user_name' => $fullName,
            'user_type' => $user->user_type
        ]);

    }

    public function logout() {
        if (Auth::user()) {
            Auth::user()->currentAccessToken()->delete();

            return response()->json([
                'message' => "You have successfully logged out"
            ]);
        } else {
            // Handle the case where the logout didn't work for some reason
            return response()->json([
                'message' => "Logout failed"
            ], 500); // You can choose an appropriate HTTP status code
        }
    }
}
