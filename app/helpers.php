<?php

// Log in user

use App\Models\User;
use Illuminate\Support\Facades\Hash;

if (!function_exists('login_user')) {
    function login_user($email, $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user && !Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Invalid login details !'
            ], 400);
        }

        // Create Token
        $token = $user->createToken($user->name)->plainTextToken;

        // Create User Data
        $user_data = [
            'name' => $user->name,
            'email' => $user->email
        ];
        // Return login details
        return response()->json([
            'user' => $user_data,
            'token' => $token
        ], 200);
    }
}
