<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function userAuthenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!$validator->passes()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (!Auth::guard('user')->attempt([
            'email'    => $request['email'],
            'password' => $request['password']
        ], $request->get('remember'))) {
            return response()->json([
                'success' => false,
                'message' => 'Either Email/Password is incorrect',
            ], 401);
        }

        $user = Auth::guard('user')->user();
        session()->put('user', $user);

        // Do NOT redirect yet — tell the front-end to show the declaration popup
        return response()->json([
            'success'      => true,
            'message'      => 'Login successful',
            'showDeclaration' => true,
        ]);
    }

    public function confirmDeclaration(Request $request)
    {
        $request->validate(['agreed' => 'required|in:1']);
        $user = Auth::guard('user')->user();

        $updateDeclaration = User::where('id', $user->id)->update([
            'declaration_agreed_at' => now(),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Declaration confirmed. Welcome!',
            'redirect' => route('scholar.create'),
        ]);
    }

    public function userRegisterUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'digits:10'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            // 'confirmed' automatically compares against password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = new User();
        $user->name     = $request->input('name');
        $user->email    = $request->input('email');
        $user->phone    = $request->input('phone_number');
        $user->password = Hash::make($request->input('password'));
        $user->save();

        Auth::guard('user')->login($user);

        return response()->json([
            'success'  => true,
            'message'  => 'Registered successfully',
            'redirect' => route('auth.login'),
        ]);
    }

    public function userLogout(Request $request)
    {
        $user = Auth::guard('user')->user();
        $updateDeclaration = User::where('id', $user->id)->update([
            'declaration_agreed_at' => null,
        ]);
        Auth::guard('user')->logout();
        $request->session()->forget('user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('auth.login');
    }
}
