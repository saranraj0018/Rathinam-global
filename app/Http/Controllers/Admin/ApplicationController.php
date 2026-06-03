<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ApplicationController extends Controller
{
    /** List with search, status filter, sorting, and pagination. */
    public function index(Request $request)
    {
        $sortable = ['application_no', 'full_name', 'programme_mode', 'status'];
        $sort = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'created_at';
        $dir  = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $applications = Application::query()
            ->withCount('documents')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->get('q');
                $query->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                        ->orWhere('application_no', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when(
                $request->filled('status') && $request->get('status') !== 'all',
                fn($query) => $query->where('status', $request->get('status'))
            )
            ->orderBy($sort, $dir)
            ->paginate(25)
            ->withQueryString();

        // Status counts for the filter pills
        $base = Application::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->get('q');
                $query->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                        ->orWhere('application_no', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            });

        $counts = ['all' => (clone $base)->count()];
        foreach (['draft', 'submitted', 'under_review', 'approved', 'rejected'] as $s) {
            $counts[$s] = (clone $base)->where('status', $s)->count();
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
}



