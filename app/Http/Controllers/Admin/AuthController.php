<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function adminAuthenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::guard('admin')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {

            return response()->json([
                'message' => 'Either Email or Password is incorrect'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'redirect' => route('dashboard')
        ]);
    }

    public function registerUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required',
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|digits:10',
            'signup_email' => 'required|email|max:255|unique:admins,email',
            'signup_password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = new Admin();
        $admin->user_name = $request->name;
        $admin->email = $request->signup_email;
        $admin->password = Hash::make($request->signup_password);
        $admin->role_id = $request->role;
        $admin->mobile_number = $request->mobile_number;
        $admin->save();

        Auth::guard('admin')->login($admin);

        return response()->json([
            'status' => true,
            'redirect' => route('dashboard')
        ]);
    }

    public function logout(): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function user_logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been logged out successfully.');
    }
}
