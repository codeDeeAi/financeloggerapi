<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Create New User
    public function store(Request $request)
    {
        // validate request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'bail|email|required|unique:users,email,except,id',
            'password' => 'bail|required|string|min:8',
        ]);

        // Create User
        $user = User::create([
            'name' =>  $request->name,
            'email' =>  $request->email,
            'password' => $request->password
        ]);

        // Login User

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
