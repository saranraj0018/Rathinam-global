<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateApplicationPdf;
use App\Models\Application;
use App\Models\ApplicationDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ZipArchive;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $sortable = ['application_no', 'full_name', 'programme_mode', 'status'];
        $sort = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'created_at';
        $dir  = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        // Reusable search scope — define once, apply twice
        $search = function ($query) use ($request) {
            if ($request->filled('q')) {
                $q = $request->get('q');
                $query->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                        ->orWhere('application_no', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            }
        };

        $applications = Application::query()
            ->withCount('documents')
            ->tap($search)
            ->when(
                $request->filled('status') && $request->get('status') !== 'all',
                fn($query) => $query->where('status', $request->get('status'))
            )
            ->orderBy($sort, $dir)
            ->paginate(25)
            ->withQueryString();

        // All status counts in ONE query instead of six
        $rawCounts = Application::query()
            ->tap($search)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = ['all' => $rawCounts->sum()];
        foreach (['draft', 'submitted', 'under_review', 'approved', 'rejected'] as $s) {
            $counts[$s] = $rawCounts->get($s, 0);
        }

        return view('admin.applications.index', compact('applications', 'counts'));
    }

    /** Full detail with all relations eager-loaded. */
    public function show(Application $application)
    {
        $application->load([
            'languages',
            'educations',
            'services',
            'projects',
            'courses',
            'aspirations',
            'documents',
        ]);

        return view('admin.applications.show', ['application' => $application]);
    }

    /** Stream a single document. */
    public function download(Application $application, ApplicationDocument $document)
    {
        abort_unless($document->application_id === $application->id, 403);

        $disk = 'public'; // set to whichever exists() returned true

        abort_unless(Storage::disk($disk)->exists($document->file_path), 404);

        return Storage::disk($disk)->download($document->file_path, $document->file_name);
    }

    public function downloadAll(Application $application)
    {
        $application->load('documents');
        abort_if($application->documents->isEmpty(), 404, 'No documents to download.');

        $disk = 'public'; // <-- must match whatever your working download() uses

        $dir = storage_path('app/tmp');
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            abort(500, 'Could not create temp directory.');
        }

        $tmpPath = $dir . '/' . $application->application_no . '_documents.zip';

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create zip archive.');
        }

        $added = 0;
        foreach ($application->documents as $doc) {
            if (Storage::disk($disk)->exists($doc->file_path)) {
                $entry = $doc->document_type . ' - ' . $doc->file_name;
                $zip->addFromString($entry, Storage::disk($disk)->get($doc->file_path));
                $added++;
            }
        }
        $zip->close();

        abort_if($added === 0 || ! file_exists($tmpPath), 404, 'No document files found on disk.');

        return response()->download($tmpPath, basename($tmpPath))->deleteFileAfterSend();
    }

    public function downloadPdf(Application $application)
    {
        $path = "generated/{$application->application_no}_application.pdf";

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }

        // Avoid dispatching duplicates on every refresh
        if (!cache()->has("pdf_dispatched_{$application->id}")) {
            GenerateApplicationPdf::dispatch($application);
            cache()->put("pdf_dispatched_{$application->id}", true, now()->addMinutes(10));
        }

        return response()->view('admin.applications.pdf-loading', [
            'application' => $application,
        ], 202);
    }
}
