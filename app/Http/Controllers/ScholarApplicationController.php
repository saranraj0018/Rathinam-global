<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarApplicationController extends Controller
{
    public function create(): View
    {
        return view('scholar.application', [
            'data' => config('scholar'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name'             => ['required', 'string', 'max:255'],
            'school'                => ['required', 'string'],
            'discipline'            => ['required', 'string'],
            'declaration_signature' => ['required', 'string'],
        ]);

        // Backend: persist the application + uploads, then take the payment.
        // On successful payment, redirect to the thank-you page.
        return redirect()->route('scholar.thankyou');
    }

    public function thankyou(): View
    {
        return view('scholar.thank-you');
    }
}
