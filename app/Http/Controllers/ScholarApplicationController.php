<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarApplicationController extends Controller
{
    /**
     * Show the multi-step Ph.D. scholar application form.
     */
    public function create(): View
    {
        // All dropdown / reference data lives in config/scholar.php so it is a
        // single source of truth shared by the Blade views and the front-end JS.
        return view('scholar.application', [
            'data' => config('scholar'),
        ]);
    }

    /**
     * Persist a submitted application.
     *
     * ───────────────────────────────────────────────────────────────────
     *  FRONTEND SCAFFOLD ONLY — backend team to implement.
     *
     *  This stub accepts the POST so the form is runnable end-to-end during
     *  UI development. Replace with real validation + storage:
     *    - Move rules into a dedicated FormRequest (ScholarApplicationRequest).
     *    - Store uploaded files (photo, certificates, merged marksheets,
     *      one-page summary, NOC / service / equivalence certs) on a disk.
     *      Front-end enforces: max 2 MB each; photo = jpg/jpeg/png;
     *      documents = pdf/jpg/jpeg/png.
     *    - Persist the application + related rows (education, service,
     *      projects, enclosures) to the DB.
     *  Field names are documented inline in the Blade step partials.
     * ───────────────────────────────────────────────────────────────────
     */
    public function store(Request $request): RedirectResponse
    {
        // Light guard so the scaffold doesn't accept totally empty posts.
        $request->validate([
            'full_name'    => ['required', 'string', 'max:255'],
            'school'       => ['required', 'string'],
            'discipline'   => ['required', 'string'],
            'declaration_signature' => ['required', 'string'],
        ]);

        // TODO(backend): real persistence here.

        return redirect()
            ->route('scholar.create')
            ->with('status', 'Application received. (Backend storage pending — this is the UI scaffold.)');
    }
}
