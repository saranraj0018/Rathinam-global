<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeclarationAgreed
{
    // app/Http/Middleware/EnsureDeclarationAgreed.php
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('user')->user();
        if (!$user) {
            return redirect()->route('auth.login');
        }

        if (is_null($user->declaration_agreed_at)) {
            return redirect()->route('auth.login');
         }
        return $next($request);
    }
}
