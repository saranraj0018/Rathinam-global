<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
            'success'  => true,
            'message'  => __($status),
            'redirect' => route('auth.login'),  
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => __($status),
        'errors'  => ['email' => [__($status)]],
    ], 422);
}
}
