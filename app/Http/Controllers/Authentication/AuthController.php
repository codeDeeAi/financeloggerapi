<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Login New User
    public function login(Request $request)
    {
        // Validate Request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'bail|required|string'
        ]);

        // Call Login function
        return login_user($request->email, $request->password);
    }

    // Logout of account
    public function logout(Request $request)
    {
        // Revoke all tokens...
        auth()->user()->tokens()->delete();

        return response('logged out', 200);
    }
}
