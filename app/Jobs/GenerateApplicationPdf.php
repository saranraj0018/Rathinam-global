<?php

namespace App\Jobs;

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// app/Jobs/GenerateApplicationPdf.php
class GenerateApplicationPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;   // allow long conversions
    public int $tries = 2;

    public function __construct(public Application $application) {}

    public function handle(): void
    {
        $application = $this->application->load([
            'languages',
            'educations',
            'services',
            'projects',
            'courses',
            'aspirations',
            'documents',
        ]);

        $images = $this->buildImages($application);

        $logoSrc = null;
        $logoPath = public_path('images/logo.png');
        if (is_file($logoPath)) {
            $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('admin.applications.pdf', [
            'app' => $application,
            'images' => $images,
            'logoSrc' => $logoSrc,
        ])->setPaper('a4')->setOption('isRemoteEnabled', true);

        Storage::disk('local')->put(
            "generated/{$application->application_no}_application.pdf",
            $pdf->output()
        );
    }

    private function buildImages(Application $application): array
    {
        $disk = 'public';
        $images = [];

        foreach ($application->documents as $doc) {
            $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));

            if (! Storage::disk($disk)->exists($doc->file_path)) {
                continue;
            }

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                $data = Storage::disk($disk)->get($doc->file_path);
                $mime = match ($ext) {
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    default => 'image/jpeg',
                };
                $images[] = [
                    'type' => $doc->document_type,
                    'name' => $doc->file_name,
                    'src'  => 'data:' . $mime . ';base64,' . base64_encode($data),
                ];
                continue;
            }

            if ($ext === 'pdf') {
                $images = array_merge($images, $this->pdfToImages($doc, $disk));
            }
        }

        return $images;
    }

    private function pdfToImages($doc, string $disk): array
    {
        $absolutePath = Storage::disk($disk)->path($doc->file_path);
        $result = [];
        $tmpDir = sys_get_temp_dir() . '/pdf_' . Str::uuid();

        try {
            if (! mkdir($tmpDir, 0755, true) && ! is_dir($tmpDir)) {
                throw new \RuntimeException("Cannot create temp dir: {$tmpDir}");
            }

            $process = new \Symfony\Component\Process\Process([
                'pdftoppm',
                '-jpeg',
                '-jpegopt',
                'quality=85',
                '-r',
                '150',
                $absolutePath,
                "{$tmpDir}/page",
            ]);
            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
            }

            $files = glob("{$tmpDir}/page-*.jpg");
            natsort($files);

            $pageNum = 1;
            foreach ($files as $file) {
                $result[] = [
                    'type' => $doc->document_type,
                    'name' => $doc->file_name . ' (page ' . $pageNum . ')',
                    'src'  => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($file)),
                ];
                $pageNum++;
            }
        } catch (\Throwable $e) {
            Log::error("PDF to image failed for doc {$doc->id}: " . $e->getMessage());
        } finally {
            // Always clean up, even on failure
            array_map('unlink', glob("{$tmpDir}/*") ?: []);
            @rmdir($tmpDir);
        }

        return $result;
    }
}
