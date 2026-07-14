<?php

namespace App\Http\Controllers;

use App\Services\DynamicAnalysisImportService;
use App\Services\CourseDetectionService;
use App\Services\ImportDuplicateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportAnalysisController extends Controller
{
    public function create()
    {
        return view('admin.import-analysis');
    }

    public function store(Request $request, DynamicAnalysisImportService $dynamicImporter)
    {
        $validated = $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx', 'max:51200'],
        ]);

        $file = $validated['excel_file'];
        $meta = $dynamicImporter->previewUploadedFile($file);

        $storedPath = $file->storeAs(
            'pending-analysis-imports',
            now()->format('YmdHis') . '-' . $this->safeFilename($file->getClientOriginalName())
        );

        $request->session()->put('pending_analysis_import', [
            'stored_path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'detected_course' => $meta['course'] ?? null,
            'sheet_names' => $meta['sheet_names'] ?? [],
        ]);

        return redirect()->route('import.analysis.confirm');
    }

    public function confirm(
        Request $request,
        CourseDetectionService $courseDetection,
        DynamicAnalysisImportService $dynamicImporter,
        ImportDuplicateService $duplicates
    )
    {
        $pending = $request->session()->get('pending_analysis_import');

        if (! is_array($pending) || empty($pending['stored_path']) || ! Storage::disk('local')->exists($pending['stored_path'])) {
            return redirect()->route('import.analysis')
                ->withErrors(['excel_file' => 'No pending import was found. Please upload the file again.']);
        }

        $originalName = (string) ($pending['original_name'] ?? 'Uploaded file.xlsx');
        $sheetNames = is_array($pending['sheet_names'] ?? null) ? $pending['sheet_names'] : [];
        $detectedCourse = $pending['detected_course'] ?? null;
        $suggestedCourse = $detectedCourse ?: $courseDetection->suggestFromText($originalName, ...$sheetNames);
        $importMeta = $dynamicImporter->metadataForImport($originalName, $suggestedCourse, $sheetNames);
        $duplicate = $duplicates->existingAnalysisDuplicate($importMeta);

        return view('admin.import-course-confirm', [
            'title' => 'Confirm Predicted Rank Course Tab',
            'eyebrow' => $detectedCourse ? 'Detected Predicted Rank Course' : 'Unknown Predicted Rank Course',
            'description' => $detectedCourse
                ? 'Review the course tab detected for this file. You can keep it, choose an existing tab, or type a new tab before importing.'
                : 'This file does not contain a known course tag. Choose whether it should create a new course tab or live under an existing tab.',
            'action' => route('import.analysis.confirm.store'),
            'skipAction' => route('import.analysis.confirm.skip'),
            'cancelRoute' => route('import.analysis'),
            'originalName' => $originalName,
            'meta' => ['title' => pathinfo($originalName, PATHINFO_FILENAME), 'sheet_names' => $sheetNames],
            'courses' => $courseDetection->courses(),
            'suggestedCourse' => $suggestedCourse,
            'detectedCourse' => $detectedCourse,
            'duplicate' => $duplicate,
        ]);
    }

    public function confirmStore(Request $request, CourseDetectionService $courseDetection, DynamicAnalysisImportService $dynamicImporter)
    {
        $pending = $request->session()->get('pending_analysis_import');

        if (! is_array($pending) || empty($pending['stored_path']) || ! Storage::disk('local')->exists($pending['stored_path'])) {
            return redirect()->route('import.analysis')
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
            $courseDetection->remember($alias, $course, 'predicted-rank-import');
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
                ->route('import.analysis')
                ->withErrors(['excel_file' => 'Import failed: ' . $exception->getMessage()]);
        } finally {
            $request->session()->forget('pending_analysis_import');
        }

        return redirect()
            ->route('import.analysis')
            ->with('status', 'Imported ' . $originalName . ' successfully.')
            ->with('import_output', 'DB predicted rank import completed. Course tab: ' . $course)
            ->with('page_route', route('analysis.show', $dataset, false))
            ->with('page_url', route('analysis.show', $dataset));
    }

    public function skip(Request $request)
    {
        $pending = $request->session()->get('pending_analysis_import');
        $originalName = is_array($pending) ? (string) ($pending['original_name'] ?? 'uploaded file') : 'uploaded file';

        if (is_array($pending) && ! empty($pending['stored_path'])) {
            Storage::disk('local')->delete((string) $pending['stored_path']);
        }

        $request->session()->forget('pending_analysis_import');

        return redirect()
            ->route('import.analysis')
            ->with('status', 'Skipped duplicate import for ' . $originalName . '. No existing data was changed.');
    }

    protected function safeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9 _-]+/', '', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        return ($name !== '' ? $name : 'predicted-rank-import-' . now()->format('YmdHis')) . '.' . strtolower($extension);
    }
}
