<?php

namespace App\Http\Controllers;

use App\Helpers\ScaffoldHelper;
use App\Services\CourseDetectionService;
use App\Services\DynamicRankImportService;
use App\Services\ImportDuplicateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ImportExcelController extends Controller
{
    public function create()
    {
        return view('import-excel');
    }

    public function store(Request $request, DynamicRankImportService $dynamicImporter)
    {
        $validated = $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx', 'max:51200'],
        ]);

        $file = $validated['excel_file'];

        if ($this->usesDbImportMode()) {
            $meta = $dynamicImporter->previewUploadedFile($file);

            $storedPath = $file->storeAs(
                'pending-rank-imports',
                now()->format('YmdHis') . '-' . $this->safeFilename($file->getClientOriginalName())
            );

            $request->session()->put('pending_rank_import', [
                'stored_path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'detected_course' => $meta['course'] ?? null,
                'sheet_names' => $meta['sheet_names'] ?? [],
            ]);

            return redirect()->route('import.excel.confirm');
        }

        $originalName = $file->getClientOriginalName();
        $safeName = $this->safeFilename($originalName);
        $dataSheetPath = base_path('data_sheets');

        if (! is_dir($dataSheetPath)) {
            mkdir($dataSheetPath, 0755, true);
        }

        $file->move($dataSheetPath, $safeName);
        $absolutePath = $dataSheetPath . DIRECTORY_SEPARATOR . $safeName;

        try {
            Artisan::call('neet:import', [
                'file' => $absolutePath,
            ]);
            $importOutput = trim(Artisan::output());

            Artisan::call('route:clear');
            Artisan::call('config:clear');

            $parts = ScaffoldHelper::parseFileName($safeName);
            $route = $this->routeUri($parts['state'], $parts['year'], $parts['descriptor']);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('import.excel')
                ->withErrors(['excel_file' => 'Import failed: ' . $exception->getMessage()])
                ->with('import_output', trim(Artisan::output()));
        }

        return redirect()
            ->route('import.excel')
            ->with('status', 'Imported ' . $safeName . ' successfully.')
            ->with('import_output', $importOutput)
            ->with('page_route', $route)
            ->with('page_url', url($route));
    }

    public function confirm(
        Request $request,
        CourseDetectionService $courseDetection,
        DynamicRankImportService $dynamicImporter,
        ImportDuplicateService $duplicates
    )
    {
        $pending = $request->session()->get('pending_rank_import');

        if (! is_array($pending) || empty($pending['stored_path']) || ! Storage::disk('local')->exists($pending['stored_path'])) {
            return redirect()->route('import.excel')
                ->withErrors(['excel_file' => 'No pending import was found. Please upload the file again.']);
        }

        $originalName = (string) ($pending['original_name'] ?? 'Uploaded file.xlsx');
        $sheetNames = is_array($pending['sheet_names'] ?? null) ? $pending['sheet_names'] : [];
        $meta = $this->metadataPreview($originalName, $courseDetection);
        $meta['sheet_names'] = $sheetNames;
        $detectedCourse = $pending['detected_course'] ?? null;
        $suggestedCourse = $detectedCourse ?: $courseDetection->suggestFromText($originalName, ...$sheetNames);
        $importMeta = $dynamicImporter->metadataForImport($originalName, $suggestedCourse, $sheetNames);
        $duplicate = $duplicates->existingResultDuplicate($importMeta);

        return view('admin.import-course-confirm', [
            'title' => 'Confirm Result Course Tab',
            'eyebrow' => $detectedCourse ? 'Detected Result Course' : 'Unknown Result Course',
            'description' => $detectedCourse
                ? 'Review the course tab detected for this file. You can keep it, choose an existing tab, or type a new tab before importing.'
                : 'This file does not contain a known course tag. Choose whether it should create a new course tab or live under an existing tab.',
            'action' => route('import.excel.confirm.store'),
            'skipAction' => route('import.excel.confirm.skip'),
            'cancelRoute' => route('import.excel'),
            'originalName' => $originalName,
            'meta' => $meta,
            'courses' => $courseDetection->courses(),
            'suggestedCourse' => $suggestedCourse,
            'detectedCourse' => $detectedCourse,
            'duplicate' => $duplicate,
        ]);
    }

    public function confirmStore(Request $request, CourseDetectionService $courseDetection, DynamicRankImportService $dynamicImporter)
    {
        $pending = $request->session()->get('pending_rank_import');

        if (! is_array($pending) || empty($pending['stored_path']) || ! Storage::disk('local')->exists($pending['stored_path'])) {
            return redirect()->route('import.excel')
                ->withErrors(['excel_file' => 'No pending import was found. Please upload the file again.']);
        }

        $validated = $request->validate([
            'course' => ['required', 'string', 'max:80'],
            'remember_mapping' => ['nullable', 'boolean'],
            'suggested_alias' => ['nullable', 'string', 'max:80'],
        ]);

        $course = $courseDetection->normalizeCourse($validated['course']);
        $originalName = (string) ($pending['original_name'] ?? 'Uploaded file.xlsx');
        $storedPath = (string) $pending['stored_path'];

        if ($request->boolean('remember_mapping', true)) {
            $alias = $validated['suggested_alias'] ?: $course;
            $courseDetection->remember($alias, $course, 'result-import');
        }

        try {
            $dataset = $dynamicImporter->importFile(
                Storage::disk('local')->path($storedPath),
                $originalName,
                $storedPath,
                $request->user()?->id,
                $course
            );
        } catch (\Throwable $exception) {
            return redirect()
                ->route('import.excel')
                ->withErrors(['excel_file' => 'Import failed: ' . $exception->getMessage()]);
        } finally {
            $request->session()->forget('pending_rank_import');
        }

        return redirect()
            ->route('import.excel')
            ->with('status', 'Imported ' . $originalName . ' successfully.')
            ->with('import_output', 'DB import completed. Course tab: ' . $course)
            ->with('page_route', route('results.show', $dataset, false))
            ->with('page_url', route('results.show', $dataset));
    }

    public function skip(Request $request)
    {
        $pending = $request->session()->get('pending_rank_import');
        $originalName = is_array($pending) ? (string) ($pending['original_name'] ?? 'uploaded file') : 'uploaded file';

        if (is_array($pending) && ! empty($pending['stored_path'])) {
            Storage::disk('local')->delete((string) $pending['stored_path']);
        }

        $request->session()->forget('pending_rank_import');

        return redirect()
            ->route('import.excel')
            ->with('status', 'Skipped duplicate import for ' . $originalName . '. No existing data was changed.');
    }

    protected function safeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9 _-]+/', '', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        return ($name !== '' ? $name : 'neet-import-' . now()->format('YmdHis')) . '.' . strtolower($extension);
    }

    protected function metadataPreview(string $filename, CourseDetectionService $courseDetection): array
    {
        try {
            $parts = ScaffoldHelper::parseFileName($filename);
        } catch (\Throwable) {
            $parts = ['state' => null, 'year' => null, 'descriptor' => null];
        }

        preg_match('/\b(20\d{2})\b/', $filename, $yearMatch);

        return [
            'title' => pathinfo($filename, PATHINFO_FILENAME),
            'year' => isset($yearMatch[1]) ? (int) $yearMatch[1] : ($parts['year'] ?? null),
            'state' => $parts['state'] ?? null,
            'descriptor' => $parts['descriptor'] ?? null,
        ];
    }

    protected function routeUri(string $state, string $year, string $descriptor): string
    {
        $parts = array_filter([$state, $year, $descriptor], fn ($part) => $part !== '');
        $uri = implode('-', $parts);
        $uri = preg_replace('/[^A-Za-z0-9]+/', '-', $uri);
        $uri = strtolower(trim($uri, '-'));

        return preg_replace('/-+/', '-', $uri);
    }

    protected function usesDbImportMode(): bool
    {
        return config('imports.mode', 'db') === 'db'
            || ! (bool) config('imports.enable_code_generation', false);
    }
}
