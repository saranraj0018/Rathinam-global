<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin: 56px 0 44px 0; }

#topspace { position: fixed; top: -48px; left: 0; right: 0; height: 8px; }
    * { font-family: "DejaVu Sans", sans-serif; }
    html, body { margin: 0; padding: 0; }
    body { font-size: 10.5px; color: #2a2a2a; line-height: 1.45; }

    /* Left/right padding lives here, NOT in @page */
    .page { padding-left: 50px; padding-right: 50px; }

    .brand { text-align: center; border-bottom: 2px solid #7a1f6e; padding-bottom: 8px; margin-bottom: 4px; }
    .brand img { width: 340px; height: auto; }
    .brand .dt { margin-top: 6px; font-size: 8px; letter-spacing: 2.5px; text-transform: uppercase; color: #7a1f6e; font-weight: bold; }

    .ttl { text-align: center; margin: 14px 0 16px; }
    .ttl h1 { font-size: 19px; margin: 0 0 4px; color: #1a1a1a; }
    .ttl .no { font-family: "DejaVu Sans Mono", monospace; font-size: 9.5px; color: #7a1f6e; letter-spacing: .5px; }
    .ttl .st { display: inline-block; margin-left: 7px; padding: 2px 10px; border-radius: 3px; font-size: 8.5px; font-weight: bold; background: #f0e6ef; color: #7a1f6e; text-transform: uppercase; }

    h2 { font-size: 10.5px; color: #fff; background: #7a1f6e; padding: 6px 11px; margin: 16px 0 9px; border-radius: 3px; letter-spacing: .5px; text-transform: uppercase; }

    table { width: 100%; border-collapse: collapse; }
    .grid td { width: 50%; padding: 6px 9px; vertical-align: top; border-bottom: 1px solid #eee; }
    .lbl { color: #999; font-size: 8px; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 2px; }
    .v { font-size: 10.5px; font-weight: bold; color: #1f1f1f; }
    .muted { color: #aaa; font-style: italic; font-size: 10px; margin: 4px 0 6px; }

    .data th { background: #f4eef3; color: #5a1751; text-align: left; padding: 6px 9px; font-size: 8px; text-transform: uppercase; letter-spacing: .4px; border-bottom: 1px solid #d8c4d4; }
    .data td { padding: 6px 9px; border-bottom: 1px solid #eee; font-size: 10px; }
    .data tr:nth-child(even) td { background: #fafafa; }

    .badge { display: inline-block; padding: 3px 10px; margin: 0 4px 4px 0; border-radius: 3px; font-size: 9px; font-weight: bold; background: #f0e6ef; color: #7a1f6e; }

    .doc { page-break-inside: avoid; margin-top: 14px; }
    .doc-cap { font-size: 9px; color: #888; margin: 2px 0 7px; }
    .doc-img { max-width: 470px; max-height: 580px; border: 1px solid #ccc; padding: 3px; }

    .endfoot { margin-top: 26px; padding-top: 8px; border-top: 1px solid #ddd; text-align: center; font-size: 8px; color: #aaa; letter-spacing: .5px; }
</style>
</head>
<body>
<div class="page">
<div id="topspace"></div>
    <div class="brand">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="RGU">
        @else
            <div style="font-size:22px;font-weight:bold;color:#7a1f6e;">RGU</div>
        @endif
        <div class="dt">Doctoral Programmes &middot; 2026–27 &middot; Application Record</div>
    </div>

    <div class="ttl">
        <h1>{{ $app->full_name }}</h1>
        <div>
            <span class="no">{{ $app->application_no }}</span>
            <span class="st">{{ ucfirst(str_replace('_', ' ', $app->status)) }}</span>
        </div>
    </div>

    <h2>Personal Details</h2>
    <table class="grid">
        <tr>
            <td><span class="lbl">Date of Birth</span><span class="v">{{ optional($app->dob)->format('d M Y') }}{{ $app->age ? ' (' . $app->age . ' yrs)' : '' }}</span></td>
            <td><span class="lbl">Gender</span><span class="v">{{ $app->gender ?: '—' }}</span></td>
        </tr>
        <tr>
            <td><span class="lbl">Nationality</span><span class="v">{{ $app->nationality ?: '—' }}</span></td>
            <td><span class="lbl">Religion</span><span class="v">{{ $app->religion ?: '—' }}</span></td>
        </tr>
        <tr>
            <td><span class="lbl">Community</span><span class="v">{{ $app->community ?: '—' }}</span></td>
            <td><span class="lbl">Differently Abled</span><span class="v">{{ $app->differently_abled ? 'Yes' : 'No' }}</span></td>
        </tr>
        <tr>
            <td><span class="lbl">Father's Name</span><span class="v">{{ $app->father_name ?: '—' }}</span></td>
            <td><span class="lbl">Mother's Name</span><span class="v">{{ $app->mother_name ?: '—' }}</span></td>
        </tr>
    </table>

    <h2>Programme</h2>
    <table class="grid">
        <tr>
            <td><span class="lbl">Specialization</span><span class="v">{{ $app->specialization ?? $app->specialization_other ?? '—' }}</span></td>
            <td><span class="lbl">Mode</span><span class="v">{{ $app->programme_mode === 'full_time' ? 'Full Time' : 'Part Time' }}</span></td>
        </tr>
        <tr>
            <td><span class="lbl">Eligibility</span><span class="v">{{ $app->eligibility_qualified ? 'Qualified (' . ($app->eligibility_exam ?? '—') . ')' : 'Not Qualified' }}</span></td>
            <td><span class="lbl">Submitted</span><span class="v">{{ optional($app->submitted_at)->format('d M Y, h:i A') ?? 'Not submitted' }}</span></td>
        </tr>
    </table>

    <h2>Contact</h2>
    <table class="grid">
        <tr>
            <td><span class="lbl">Mobile</span><span class="v">{{ $app->mobile ?: '—' }}</span></td>
            <td><span class="lbl">Email</span><span class="v">{{ $app->email ?: '—' }}</span></td>
        </tr>
        <tr><td colspan="2"><span class="lbl">Current Address</span><span class="v">{{ $app->address_current ?: '—' }}</span></td></tr>
        <tr><td colspan="2"><span class="lbl">Permanent Address</span><span class="v">{{ $app->address_same ? 'Same as current' : ($app->address_permanent ?: '—') }}</span></td></tr>
    </table>

    <h2>Languages</h2>
    @if($app->languages->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Language</th><th>Read</th><th>Write</th><th>Speak</th></tr></thead>
            <tbody>
                @foreach($app->languages as $l)
                    <tr><td>{{ $l->language }}</td><td>{{ $l->can_read ? 'Yes' : '—' }}</td><td>{{ $l->can_write ? 'Yes' : '—' }}</td><td>{{ $l->can_speak ? 'Yes' : '—' }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Educational Qualifications</h2>
    @if($app->educations->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Level</th><th>Institution</th><th>Subjects</th><th>Marks</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($app->educations as $e)
                    <tr><td>{{ $e->education_level }}</td><td>{{ $e->institution }}</td><td>{{ $e->subjects }}</td><td>{{ $e->marks }}</td><td>{{ optional($e->passing_date)->format('d M Y') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Work Experience</h2>
    @if($app->services->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Designation</th><th>Institution</th><th>Duration</th><th>Period</th></tr></thead>
            <tbody>
                @foreach($app->services as $s)
                    <tr><td>{{ $s->designation }}</td><td>{{ $s->institution }}</td><td>{{ $s->total_duration }}</td><td>{{ optional($s->from_date)->format('d M Y') }} — {{ optional($s->to_date)->format('d M Y') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Projects</h2>
    @if($app->projects->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Title</th><th>Status</th></tr></thead>
            <tbody>@foreach($app->projects as $p)<tr><td>{{ $p->title }}</td><td>{{ $p->status }}</td></tr>@endforeach</tbody>
        </table>
    @endif

    <h2>Courses</h2>
    @if($app->courses->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Course</th><th>Status</th></tr></thead>
            <tbody>@foreach($app->courses as $c)<tr><td>{{ $c->course_name }}</td><td>{{ $c->completed ? 'Completed' : 'Pending' }}</td></tr>@endforeach</tbody>
        </table>
    @endif

    <h2>Career Aspirations</h2>
    @if($app->aspirations->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <p>@foreach($app->aspirations as $asp)<span class="badge">{{ $asp->aspiration }}</span>@endforeach</p>
    @endif

    <h2>Uploaded Documents</h2>
    @if($app->documents->isEmpty())
        <p class="muted">No documents uploaded.</p>
    @else
        <table class="data">
            <thead><tr><th>Type</th><th>File</th><th>Size</th></tr></thead>
            <tbody>@foreach($app->documents as $d)<tr><td>{{ $d->document_type }}</td><td>{{ $d->file_name }}</td><td>{{ $d->file_size_human }}</td></tr>@endforeach</tbody>
        </table>
    @endif

    @foreach($images as $img)
        <div class="doc">
            <h2>Document · {{ $img['type'] }}</h2>
            <p class="doc-cap">{{ $img['name'] }}</p>
            <img class="doc-img" src="{{ $img['src'] }}">
        </div>
    @endforeach

    <div class="endfoot">
        {{ $app->application_no }} &middot; {{ $app->full_name }} &middot; Generated {{ now()->format('d M Y') }}
    </div>

</div>
</body>
</html>
