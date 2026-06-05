{{-- resources/views/admin/applications/pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 11px; color: #222; margin: 0; }
    h1 { font-size: 20px; margin: 0 0 2px; }
    h2 { font-size: 13px; color: #fff; background: #b8923f; padding: 6px 10px;
         margin: 22px 0 10px; border-radius: 4px; }
    .sub { color: #777; font-size: 11px; margin: 0 0 14px; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 5px 8px; vertical-align: top; }
    .grid td { width: 50%; border-bottom: 1px solid #eee; }
    .label { color: #888; font-size: 9px; text-transform: uppercase;
             letter-spacing: .5px; display: block; margin-bottom: 2px; }
    .val { font-size: 11px; font-weight: bold; }
    .data th { background: #f3f3f3; text-align: left; padding: 6px 8px;
               font-size: 9px; text-transform: uppercase; border-bottom: 1px solid #ddd; }
    .data td { border-bottom: 1px solid #eee; }
    .badge { display: inline-block; padding: 3px 9px; border-radius: 4px;
             font-size: 10px; font-weight: bold; background: #eee; }
    .doc-img { max-width: 100%; max-height: 700px; margin: 6px 0 18px;
               border: 1px solid #ccc; }
    .page-break { page-break-before: always; }
    .muted { color: #999; font-style: italic; }
</style>
</head>
<body>

    <h1>{{ $app->full_name }}</h1>
    <p class="sub">{{ $app->application_no }} &middot; {{ ucfirst(str_replace('_', ' ', $app->status)) }}</p>

    {{-- PERSONAL --}}
    <h2>Personal Details</h2>
    <table class="grid">
        <tr>
            <td><span class="label">Date of Birth</span><span class="val">{{ optional($app->dob)->format('d M Y') }} ({{ $app->age }} yrs)</span></td>
            <td><span class="label">Gender</span><span class="val">{{ $app->gender }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Nationality</span><span class="val">{{ $app->nationality }}</span></td>
            <td><span class="label">Religion</span><span class="val">{{ $app->religion }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Father's Name</span><span class="val">{{ $app->father_name }}</span></td>
            <td><span class="label">Mother's Name</span><span class="val">{{ $app->mother_name }}</span></td>
        </tr>
    </table>

    {{-- PROGRAMME --}}
    <h2>Programme</h2>
    <table class="grid">
        <tr>
            <td><span class="label">Specialization</span><span class="val">{{ $app->specialization ?? $app->specialization_other ?? '—' }}</span></td>
            <td><span class="label">Mode</span><span class="val">{{ $app->programme_mode === 'full_time' ? 'Full Time' : 'Part Time' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Eligibility</span><span class="val">{{ $app->eligibility_qualified ? 'Qualified' : 'Not Qualified' }}</span></td>
            <td><span class="label">Submitted</span><span class="val">{{ optional($app->submitted_at)->format('d M Y') ?? 'Not submitted' }}</span></td>
        </tr>
    </table>

    {{-- CONTACT --}}
    <h2>Contact</h2>
    <table class="grid">
        <tr>
            <td><span class="label">Mobile</span><span class="val">{{ $app->mobile }}</span></td>
            <td><span class="label">Email</span><span class="val">{{ $app->email }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Current Address</span><span class="val">{{ $app->address_current }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Permanent Address</span><span class="val">{{ $app->address_same ? 'Same as current' : $app->address_permanent }}</span></td>
        </tr>
    </table>

    {{-- LANGUAGES --}}
    <h2>Languages</h2>
    @if($app->languages->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Language</th><th>Read</th><th>Write</th><th>Speak</th></tr></thead>
            <tbody>
                @foreach($app->languages as $l)
                    <tr>
                        <td>{{ $l->language }}</td>
                        <td>{{ $l->can_read ? 'Yes' : '—' }}</td>
                        <td>{{ $l->can_write ? 'Yes' : '—' }}</td>
                        <td>{{ $l->can_speak ? 'Yes' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- EDUCATION --}}
    <h2>Educational Qualifications</h2>
    @if($app->educations->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Level</th><th>Institution</th><th>Subjects</th><th>Marks</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($app->educations as $e)
                    <tr>
                        <td>{{ $e->education_level }}</td>
                        <td>{{ $e->institution }}</td>
                        <td>{{ $e->subjects }}</td>
                        <td>{{ $e->marks }}</td>
                        <td>{{ optional($e->passing_date)->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- EXPERIENCE --}}
    <h2>Work Experience</h2>
    @if($app->services->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Designation</th><th>Institution</th><th>Duration</th><th>Period</th></tr></thead>
            <tbody>
                @foreach($app->services as $s)
                    <tr>
                        <td>{{ $s->designation }}</td>
                        <td>{{ $s->institution }}</td>
                        <td>{{ $s->total_duration }}</td>
                        <td>{{ optional($s->from_date)->format('d M Y') }} — {{ optional($s->to_date)->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- PROJECTS --}}
    <h2>Projects</h2>
    @if($app->projects->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Title</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($app->projects as $p)
                    <tr><td>{{ $p->title }}</td><td>{{ $p->status }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- COURSES --}}
    <h2>Courses</h2>
    @if($app->courses->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <table class="data">
            <thead><tr><th>Course</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($app->courses as $c)
                    <tr><td>{{ $c->course_name }}</td><td>{{ $c->completed ? 'Completed' : 'Pending' }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ASPIRATIONS --}}
    <h2>Career Aspirations</h2>
    @if($app->aspirations->isEmpty())
        <p class="muted">No records available.</p>
    @else
        <p>
            @foreach($app->aspirations as $a)
                <span class="badge">{{ $a->aspiration }}</span>
            @endforeach
        </p>
    @endif

    {{-- DOCUMENT LIST --}}
    <h2>Uploaded Documents</h2>
    @if($app->documents->isEmpty())
        <p class="muted">No documents uploaded.</p>
    @else
        <table class="data">
            <thead><tr><th>Type</th><th>File</th><th>Size</th></tr></thead>
            <tbody>
                @foreach($app->documents as $d)
                    <tr>
                        <td>{{ $d->document_type }}</td>
                        <td>{{ $d->file_name }}</td>
                        <td>{{ $d->file_size_human }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- EMBEDDED IMAGE DOCUMENTS --}}
    @foreach($images as $img)
        <div class="page-break"></div>
        <h2>{{ $img['type'] }}</h2>
        <p class="sub">{{ $img['name'] }}</p>
        <img class="doc-img" src="{{ $img['src'] }}">
    @endforeach

</body>
</html>
