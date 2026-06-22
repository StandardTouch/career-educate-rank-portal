<?php

namespace App\Http\Controllers;

use App\Services\DynamicAnalysisImportService;
use Illuminate\Http\Request;

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

        try {
            $dataset = $dynamicImporter->importUploadedFile($file, $request->user()?->id);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('import.analysis')
                ->withErrors(['excel_file' => 'Import failed: ' . $exception->getMessage()]);
        }

        return redirect()
            ->route('import.analysis')
            ->with('status', 'Imported ' . $file->getClientOriginalName() . ' successfully.')
            ->with('import_output', 'DB analysis import completed.')
            ->with('page_route', route('analysis.show', $dataset, false))
            ->with('page_url', route('analysis.show', $dataset));
    }
}
