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
            'full_name'    => ['required', 'string', 'max:255'],
            'school'       => ['required', 'string'],
            'discipline'   => ['required', 'string'],
            'declaration_signature' => ['required', 'string'],
        ]);


        return redirect()
            ->route('scholar.create')
            ->with('status', 'Application received. (Backend storage pending — this is the UI scaffold.)');
    }
}
