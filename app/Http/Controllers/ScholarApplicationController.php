<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationSubmitted;
use App\Models\Application;
use App\Models\Payment;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScholarApplicationController extends Controller
{

    private function resolveEnclosureSource(?string $source): ?string
    {
        if (empty($source)) {
            return null; // manual item (e.g. foreign-degree)
        }

        if (preg_match('/^education\[([^\]]+)\]\[marksheet\]$/', $source, $m)) {
            return 'marksheet_' . $m[1];
        }

        $aliases = [
            'community_cert' => 'community_certificate',
        ];
        return $aliases[$source] ?? $source;
    }


    private function buildEnclosureStatus(Application $app): array
    {
        $existingTypes = $app->documents()->pluck('document_type')->all();
        $savedChecked  = $app->enclosures()
            ->pluck('checked', 'enclosure_key')   // [key => 0/1]
            ->map(fn($v) => (bool) $v)
            ->all();

        $status = [];
        foreach (config('scholar.enclosures', []) as $item) {
            $key      = $item['key'];
            $docType  = $this->resolveEnclosureSource($item['source'] ?? null);

            $hasFile    = $docType && in_array($docType, $existingTypes, true);
            $wasChecked = $savedChecked[$key] ?? false;

            $status[$key] = [
                'doc_type' => $docType,
                'checked'  => $hasFile || $wasChecked,   // file OR previously ticked
            ];
        }

        return $status;
    }

    public function create(): View
    {
        $user = Auth::guard('user')->user();
        $app  = $this->currentDraft();

        $draft = [];
        $enclosureStatus = [];
        $status = '';
        $savedFiles = [];

        if ($app) {
            $app->load(['enclosures', 'documents']);
            // ← ADD THIS BLOCK
            $savedFiles = $app->documents
                ->mapWithKeys(fn($d) => [$d->document_type => [
                    'name' => $d->file_name,
                    'url'  => Storage::url($d->file_path),
                ]])
                ->all();
            $draft = [
                'school'               => $app->school,
                'discipline'           => $app->discipline,
                'specialization'       => $app->specialization,
                'specialization_other' => $app->specialization_other,
                'programme_mode'       => $app->programme_mode,
                'enclosures_confirm'   => (bool) $app->enclosures_confirm,
                'payment_status'       => $app->payment_status ?? 'payment_pending',
                'enclosures' => $app->enclosures
                    ->mapWithKeys(fn($e) => [$e->enclosure_key => (bool) $e->checked])
                    ->all(),
                'files' => $savedFiles,
            ];
            $status          = $app->status ?? '';
            $enclosureStatus = $this->buildEnclosureStatus($app);
        }

        return view('scholar.application', [
            'data'            => config('scholar'),
            'user'            => $user,
            'draft'           => $draft,
            'status'          => $status,
            'enclosureStatus' => $enclosureStatus,
            'savedFiles'      => $savedFiles, 
        ]);
    }


    private function stepRules(string $step): array
    {

        return match ($step) {
            'programme' => [
                'school'               => ['nullable', 'string', 'max:255'],
                'discipline'           => ['nullable', 'string', 'max:255'],
                'specialization'       => ['nullable', 'string', 'max:255'],
                'specialization_other' => ['nullable', 'string', 'max:255'],
                'engineering_stream'   => ['required'],
                'programme_mode'       => ['nullable', Rule::in(['full_time', 'part_time', 'FT-Startup', 'Integrated', 'FT', 'PT'])],
            ],
            'personal' => [
                'full_name'         => ['nullable', 'string', 'max:255'],
                'dob'               => ['nullable', 'date'],
                'age'               => ['nullable', 'integer', 'min:0', 'max:120'],
                'gender'            => ['nullable', Rule::in(['Male', 'Female', 'Transgender', 'Other'])],
                'single_girl_child' => ['nullable'],
                'nationality'       => ['nullable', 'string', 'max:255'],
                'religion'          => ['nullable', 'string', 'max:255'],
                'community'         => ['nullable', 'string', 'max:255'],
                'differently_abled' => ['nullable'],
                'father_name'       => ['nullable', 'string', 'max:255'],
                'mother_name'       => ['nullable', 'string', 'max:255'],
                'mobile'            => ['nullable', 'string', 'max:20'],
                'email'             => ['nullable', 'email', 'max:255'],
                'address_current'   => ['nullable', 'string'],
                'address_same'      => ['nullable'],
                'address_permanent' => ['nullable', 'string'],
                'languages'         => ['nullable', 'array'],
                // files
                'community_certificate'    => ['nullable', 'file', 'max:2048'],
                'disability_certificate'   => ['nullable', 'file', 'max:2048'],
            ],
            'education' => [
                'education' => ['nullable', 'array'],
            ],
            'eligibility' => [
                'eligibility_qualified' => ['nullable'],
                'eligibility_exam'      => ['nullable', 'string', 'max:255'],
                'eligibility_certificate'      => ['nullable', 'file', 'max:2048'],
            ],
            'experience' => [
                'service'              => ['nullable', 'array'],
                'total_service_years'  => ['nullable', 'integer', 'min:0'],
                'total_service_months' => ['nullable', 'integer', 'min:0'],
            ],
            'research' => [
                'projects'           => ['nullable', 'array'],
                'career_aspirations' => ['nullable', 'array'],
                'career_other'       => ['nullable', 'string', 'max:255'],
                'summary_document'   => ['nullable', 'file', 'max:2048'],
            ],
            'enclosures' => [
                'enclosures'          => ['nullable', 'array'],
                'enclosures_confirm'  => ['nullable'],
                'noc_document'        => ['nullable', 'file', 'max:2048'],
                'service_certificate' => ['nullable', 'file', 'max:2048'],
                'equivalence_certificate'    => ['nullable', 'file', 'max:2048'],
            ],
            'declaration' => [
                'declaration_agree'     => ['nullable'],
                'declaration_date'      => ['nullable', 'date'],
                'declaration_station'   => ['nullable', 'string', 'max:255'],
                'declaration_signature' => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
    }


    public function draft(Request $request)
    {
        // Explicit auth check that returns JSON (so the front-end can react),
        // instead of relying on abort_unless() inside currentDraft() which
        // produces an HTML/redirect response the AJAX call cannot read.
        $user = Auth::guard('user')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'reason'  => 'unauthenticated',
                'message' => 'Your session has expired. Please log in again.',
            ], 401);
        }

        $app = Application::where('user_id', $user->id)
            ->where('status', 'draft')
            ->latest('id')
            ->first();

        if (!$app) {
            // No saved draft for THIS user → prefill from the user record.
            return response()->json([
                'success' => true,
                'draft'   => [
                    'full_name'   => strtoupper($user->name ?? ''),
                    'email'       => $user->email ?? '',
                    'mobile'      => $user->mobile ?? $user->phone ?? '',
                    'nationality' => 'Indian',
                ],
                'current_step'    => 'programme',
                'payment_status'  => 'unpaid',
                'completed_steps' => [],
            ]);
        }

        $app->load(['languages', 'educations', 'services', 'projects', 'courses', 'aspirations', 'enclosures', 'documents']);
        $decl = is_array($app->declaration)
            ? $app->declaration
            : (json_decode($app->declaration, true) ?: []);
        $payload = [
            'school'               => $app->school,
            'discipline'           => $app->discipline,
            'specialization'       => $app->specialization,
            'specialization_other' => $app->specialization_other,
            'programme_mode'       => $app->programme_mode,
            'full_name'            => $app->full_name,
            'dob'                  => optional($app->dob)->format('Y-m-d'),
            'age'                  => $app->age,
            'gender'               => $app->gender,
            'single_girl_child'    => (bool) $app->single_girl_child,
            'nationality'          => $app->nationality,
            'religion'             => $app->religion,
            'community'            => $app->community,
            'differently_abled'    => (bool) $app->differently_abled,
            'father_name'          => $app->father_name,
            'mother_name'          => $app->mother_name,
            'mobile'               => $app->mobile,
            'email'                => $app->email,
            'address_current'      => $app->address_current,
            'address_same'         => (bool) $app->address_same,
            'address_permanent'    => $app->address_permanent,
            'eligibility_qualified' => (bool) $app->eligibility_qualified,
            'eligibility_exam'     => $app->eligibility_exam,
            'total_service_years'  => $app->total_service_years,
            'total_service_months' => $app->total_service_months,
            'career_other'         => $app->career_other,
            'enclosures_confirm'   => (bool) $app->enclosures_confirm,
            'engineering_stream'   =>  $app->engineering_stream,
            // ── declaration (read from the JSON `declaration` column) ──
            'declaration_agree'     => (bool) ($decl['agree'] ?? false),
            'declaration_date'      => $decl['date'] ?? null,
            'declaration_station'   => $decl['station'] ?? null,
            'declaration_signature' => $decl['signature'] ?? null,
            'languages'            => $app->languages->map(fn($l) => [
                'name'   => $l->language,
                'skills' => array_values(array_filter([
                    $l->can_read  ? 'R' : null,
                    $l->can_write ? 'W' : null,
                    $l->can_speak ? 'S' : null,
                ])),
            ])->values(),
            'education'          => $app->educations->mapWithKeys(fn($e) => [
                $e->education_level => [
                    'subjects'    => $e->subjects,
                    'institution' => $e->institution,
                    'passing'     => optional($e->passing_date)->format('Y-m'),
                    'marks'       => $e->marks,
                ],
            ]),
            'service'            => $app->services->map(fn($s) => [
                'designation' => $s->designation,
                'institution' => $s->institution,
                'from'        => optional($s->from_date)->format('Y-m'),
                'to'          => optional($s->to_date)->format('Y-m'),
                'total'       => $s->total_duration,
            ])->values(),
            'projects'           => $app->projects->map(fn($p) => [
                'title'  => $p->title,
                'status' => $p->status,
            ])->values(),
            'career_aspirations' => $app->aspirations->pluck('aspiration')->values(),
            'courses'            => $app->courses->mapWithKeys(fn($c) => [$c->course_name => (bool) $c->completed]),
            'enclosures'         => $app->enclosures->mapWithKeys(fn($e) => [$e->enclosure_key => (bool) $e->checked]),
            'files'              => $app->documents->mapWithKeys(fn($d) => [$d->document_type => [
                'name' => $d->file_name,
                'url'  => Storage::url($d->file_path),
            ]]),
        ];

        return response()->json([
            'success'         => true,
            'application_no'  => $app->application_no,
            'status'          => $app->status,
            'current_step'    => $app->current_step ?? 'programme',
            'payment_status'  => $app->payment_status ?? 'unpaid',
            'completed_steps' => $app->completed_steps ?? [],  // JSON column
            'draft'           => $payload,
        ]);
    }

    public function saveStep(Request $request, string $step)
    {
        $data = $request->validate($this->stepRules($step));
        $app  = $this->currentDraft(orCreate: true);
        match ($step) {
            'programme'   => $this->saveProgramme($app, $request, $data),
            'personal'    => $this->savePersonal($app, $request, $data),
            'education'   => $this->saveEducation($app, $request, $data),
            'eligibility' => $this->saveEligibility($app, $request, $data),
            'experience'  => $this->saveExperience($app, $request, $data),
            'research'    => $this->saveResearch($app, $request, $data),
            'enclosures'  => $this->saveEnclosures($app, $request, $data),
            'declaration' => $this->saveDeclaration($app, $request, $data),
            default       => null,
        };

        // Track completed steps
        $completed = $app->completed_steps ?? [];
        if (!in_array($step, $completed)) {
            $completed[] = $step;
        }

        // Step order. After declaration -> preview (payment happens FROM preview).
        $allSteps = ['programme', 'personal', 'education', 'eligibility', 'experience', 'research', 'enclosures', 'declaration', 'preview'];
        $currentIndex = array_search($step, $allSteps);
        $nextStep = $allSteps[$currentIndex + 1] ?? 'preview';

        $app->completed_steps = $completed;
        $app->current_step    = $nextStep;
        $app->save();

        return response()->json([
            'success'         => true,
            'message'         => 'Saved.',
            'step'            => $step,
            'application_no'  => $app->application_no,
            'completed_steps' => $completed,
            'current_step'    => $nextStep,   // 'preview' after declaration
        ]);
    }


    /** Final submit after preview */
    public function submit(Request $request)
    {
        $app = $this->currentDraft();
        abort_unless($app, 404);
        abort_unless($app->payment_status === 'paid', 403, 'Payment required before submission.');

        $app->status       = 'submitted';
        $app->submitted_at = now();
        $app->save();
        if (!empty($app->email)) {
            Mail::to($app->email)
                // ->cc('admissions@rgu.ac.in')   // university copy
                ->send(new ApplicationSubmitted($app));
        }
        return response()->json([
            'success'  => true,
            'message'  => 'Application submitted successfully.',
            'redirect' => route('scholar.thankyou'),
        ]);
    }

    /* ───────────────────────── helpers ───────────────────────── */

    private function currentDraft(bool $orCreate = false): ?Application
    {
        $user = Auth::guard('user')->user();
        abort_unless($user, 401);

        $app = Application::where('user_id', $user->id)
            ->where('status', 'draft')
            ->latest('id')
            ->first();

        if (!$app && $orCreate) {
            $app = new Application();
            $app->user_id        = $user->id;
            $app->application_no  = $this->makeApplicationNo();
            $app->status          = 'draft';
            // NOT-NULL columns need placeholder defaults so a partial draft can save:
            $app->programme_mode  = 'full_time';
            $app->full_name       = $user->name ?? '';
            $app->dob             = now();
            $app->gender          = 'Other';
            $app->religion        = '';
            $app->father_name     = '';
            $app->mother_name     = '';
            $app->mobile          = $user->phone ?? '';
            $app->email           = $user->email;
            $app->address_current = '';
            $app->address_permanent = '';
            $app->save();
        }

        return $app;
    }

    private function makeApplicationNo(): string
    {
        return 'PHD-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
    }

    private function storeFile(Application $app, Request $request, string $field, string $docType): void
    {
        if (!$request->hasFile($field)) {
            return;
        }
        $file = $request->file($field);
        $path = $file->store("applications/{$app->id}", 'public');

        // one document per type — replace existing
        $app->documents()->updateOrCreate(
            ['document_type' => $docType],
            [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]
        );
    }

    private function saveProgramme(Application $app, Request $r, array $d): void
    {
        $specialization = $d['specialization'] ?? null;
        if ($specialization === '__other__' || $specialization === null || $specialization === '') {
            $specialization = $d['specialization_other'] ?? $specialization;
        }

        $app->fill([
            'school'               => $d['school'] ?? $app->school,
            'discipline'           => $d['discipline'] ?? $app->discipline,
            'specialization'       => $specialization,
            'specialization_other' => $d['specialization_other'] ?? null,
            'engineering_stream' => $d['engineering_stream'],
            'programme_mode'       => $this->normMode($d['programme_mode'] ?? $app->programme_mode),
        ]);
        $app->save();

        $this->storeFile($app, $r, 'photo', 'photo');
        // photo_existing flag is a no-op — existing file is kept automatically.
    }

    private function savePersonal(Application $app, Request $r, array $d): void
    {
        $app->fill([
            'full_name'         => $d['full_name'] ?? $app->full_name,
            'dob'               => $d['dob'] ?? $app->dob,
            'age'               => $d['age'] ?? $app->age,
            'gender'            => $d['gender'] ?? $app->gender,
            'single_girl_child' => $r->boolean('single_girl_child'),
            'nationality'       => $d['nationality'] ?? $app->nationality,
            'religion'          => $d['religion'] ?? $app->religion,
            'community'         => $d['community'] ?? null,
            'differently_abled' => $r->boolean('differently_abled'),
            'father_name'       => $d['father_name'] ?? $app->father_name,
            'mother_name'       => $d['mother_name'] ?? $app->mother_name,
            'mobile'            => $d['mobile'] ?? $app->mobile,
            'email'             => $d['email'] ?? $app->email,
            'address_current'   => $d['address_current'] ?? $app->address_current,
            'address_same'      => $r->boolean('address_same'),
            'address_permanent' => $d['address_permanent'] ?? $app->address_permanent,
        ]);
        $app->save();

        $this->storeFile($app, $r, 'community_certificate', 'community_certificate');
        $isAbled = in_array($r->input('differently_abled'), ['Yes', '1', 1, true, 'yes'], true);

        if ($isAbled) {
            $this->storeFile($app, $r, 'disability_certificate', 'disability_certificate');
        } else {
            $doc = $app->documents()->where('document_type', 'disability_certificate')->first();
            if ($doc) {
                Storage::disk('public')->delete($doc->file_path);
                $doc->delete();
            }
        }

        // languages: replace the set
        $app->languages()->delete();
        foreach ((array) $r->input('languages', []) as $lang) {
            $name = trim($lang['name'] ?? '');
            if ($name === '') continue;
            $skills = (array) ($lang['skills'] ?? []);
            $app->languages()->create([
                'language'  => $name,
                'can_read'  => in_array('R', $skills, true),
                'can_write' => in_array('W', $skills, true),
                'can_speak' => in_array('S', $skills, true),
            ]);
        }
    }

    private function saveEducation(\App\Models\Application $app, \Illuminate\Http\Request $r, array $d): void
    {
        $postedEducation = (array) $r->input('education', []);
        $levelsKept = [];
        $app->educations()->delete();
        foreach ($postedEducation as $level => $row) {
            $row = (array) $row;
            $hasData = collect($row)
                ->except(['marksheet_removed'])
                ->filter(fn($v) => filled($v))
                ->isNotEmpty();

            $removed = ($row['marksheet_removed'] ?? '0') === '1';
            $hasNewFile = $r->hasFile("education.$level.marksheet");
            if ($removed) {
                $this->deleteDocument($app, "marksheet_$level");
            }
            if (!$hasData) {
                if (!$hasNewFile) {
                    $this->deleteDocument($app, "marksheet_$level");
                }
                continue;
            }

            // ── 3. Row has data → (re)create the education record ──
            $passing = $row['passing'] ?? null; // "Y-m"
            $app->educations()->create([
                'education_level' => $level,
                'subjects'        => $row['subjects'] ?? null,
                'institution'     => $row['institution'] ?? null,
                'passing_date'    => $passing ? $passing . '-01' : null,
                'marks'           => $row['marks'] ?? null,
            ]);
            $levelsKept[] = $level;

            // ── 4. File handling for a data-bearing row ──
            if ($hasNewFile) {
                $this->storeFile($app, $r, "education.$level.marksheet", "marksheet_$level");
            }
        }

        $app->save();
    }

    private function deleteDocument(\App\Models\Application $app, string $docType): void
    {
        $doc = $app->documents()->where('document_type', $docType)->first();
        if ($doc) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
            $doc->delete();
        }
    }

    private function saveEligibility(Application $app, Request $r, array $d): void
    {
        $app->fill([
            'eligibility_qualified' => $r->boolean('eligibility_qualified')
                || in_array($r->input('eligibility_qualified'), ['Yes', '1', 'yes', true], true),
            'eligibility_exam'      => $d['eligibility_exam'] ?? null,
        ]);
        $app->save();
        $this->storeFile($app, $r, 'eligibility_certificate', 'eligibility_certificate');
    }

    private function saveExperience(Application $app, Request $r, array $d): void
    {
        $app->fill([
            'total_service_years'  => (int) ($d['total_service_years'] ?? 0),
            'total_service_months' => (int) ($d['total_service_months'] ?? 0),
        ]);
        $app->save();

        $app->services()->delete();
        foreach ((array) $r->input('service', []) as $row) {
            if (empty(trim($row['designation'] ?? '')) && empty(trim($row['institution'] ?? ''))) continue;
            $from = $row['from'] ?? null;
            $to   = $row['to'] ?? null;
            $app->services()->create([
                'designation'    => $row['designation'] ?? '',
                'institution'    => $row['institution'] ?? '',
                'from_date'      => $from ? $from . '-01' : null,
                'to_date'        => $to ? $to . '-01' : null,
                'total_duration' => $row['total'] ?? null,
            ]);
        }
    }

    private function saveResearch(Application $app, Request $r, array $d): void
    {
        $app->career_other = $d['career_other'] ?? null;
        $app->save();
        $this->storeFile($app, $r, 'summary_document', 'summary_document');

        $app->projects()->delete();
        foreach ((array) $r->input('projects', []) as $row) {
            $title = trim($row['title'] ?? '');
            if ($title === '') continue;
            $app->projects()->create([
                'title'  => $title,
                'status' => $row['status'] ?? null,
            ]);
        }

        // courses: any field named course_<key>
        $app->courses()->delete();
        foreach ($r->all() as $key => $val) {
            if (str_starts_with($key, 'course_')) {
                $app->courses()->create([
                    'course_name' => substr($key, 7),
                    'completed'   => in_array($val, ['Yes', '1', 1, true, 'yes'], true),
                ]);
            }
        }

        $app->aspirations()->delete();
        foreach ((array) $r->input('career_aspirations', []) as $a) {
            if (trim($a) === '') continue;
            $app->aspirations()->create(['aspiration' => $a]);
        }
    }

    private function saveEnclosures(Application $app, Request $r, array $d): void
    {
        $app->enclosures_confirm = $r->boolean('enclosures_confirm');
        $app->save();
        $this->storeFile($app, $r, 'noc_document', 'noc_document');
        $this->storeFile($app, $r, 'service_certificate', 'service_certificate');
        $this->storeFile($app, $r, 'equivalence_certificate', 'equivalence_certificate');

        $existingTypes = $app->documents()->pluck('document_type')->all();
        $posted        = (array) $r->input('enclosures', []);

        $app->enclosures()->delete();

        foreach (config('scholar.enclosures', []) as $item) {
            $key     = $item['key'];
            $docType = $this->resolveEnclosureSource($item['source'] ?? null);

            $checked = $docType
                ? in_array($docType, $existingTypes, true)   // auto: file exists?
                : (bool) ($posted[$key] ?? false);           // manual: trust POST

            $app->enclosures()->create([
                'enclosure_key' => $key,
                'checked'       => $checked,
            ]);
        }
    }


    private function saveDeclaration(Application $app, Request $r, array $d): void
    {

        $app->declaration = [
            'agree'     => $r->boolean('declaration_agree'),
            'date'      => $d['declaration_date']      ?? null,
            'station'   => $d['declaration_station']   ?? null,
            'signature' => $d['declaration_signature'] ?? null,
            'signed_at' => now()->toDateTimeString(),
        ];
        $app->save();
    }

    private function normMode(?string $mode): string
    {
        return match ($mode) {
            'PT', 'part_time' => 'PT',
            'Startup Based Ph.D', 'FT-Startup' => 'FT-Startup',
            'Integrated PG + Ph.D', 'Integrated' =>  'Integrated',
            'FT', 'full_time' => 'FT',
            default           => 'FT',
        };
    }

    /** Replace with your real Annexure-1 data source. */
    private function annexureData(): array
    {
        return [
            'schools'         => [],          // your cascade JSON
            'programme_modes' => ['FT' => 'full_time', 'PT' => 'part_time', 'full_time' => 'Full Time', 'part_time' => 'Part Time', 'FT-Startup' => 'Startup Based Ph.D', 'Integrated' => 'Integrated PG + Ph.D'],
        ];
    }

    public function thankyou(): View
    {
        return view('scholar.thank-you');
    }
}
