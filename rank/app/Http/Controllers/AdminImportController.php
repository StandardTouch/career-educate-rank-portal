<?php

namespace App\Http\Controllers;

use App\Models\AnalysisImport;
use App\Models\Import;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminImportController extends Controller
{
    public function index(): View
    {
        $resultImports = Import::with('dataset')
            ->latest('id')
            ->paginate(15, ['*'], 'result_page');

        $predictedRankImports = AnalysisImport::with('analysisDataset')
            ->latest('id')
            ->paginate(15, ['*'], 'predicted_page');

        return view('admin.imports.index', compact('resultImports', 'predictedRankImports'));
    }

    public function destroyResult(Import $import): RedirectResponse
    {
        $name = $import->original_filename;

        DB::transaction(function () use ($import): void {
            $dataset = $import->dataset;

            if ($dataset) {
                $paths = $dataset->imports()
                    ->pluck('stored_path')
                    ->filter()
                    ->values()
                    ->all();

                $dataset->delete();
                $this->deleteStoredFiles($paths);

                return;
            }

            $path = $import->stored_path;
            $import->delete();
            $this->deleteStoredFiles([$path]);
        });

        return redirect()
            ->route('admin.imports')
            ->with('status', 'Deleted result import "' . $name . '" and removed it from the portal.');
    }

    public function destroyPredictedRank(AnalysisImport $analysisImport): RedirectResponse
    {
        $name = $analysisImport->original_filename;

        DB::transaction(function () use ($analysisImport): void {
            $dataset = $analysisImport->analysisDataset;

            if ($dataset) {
                $paths = $dataset->imports()
                    ->pluck('stored_path')
                    ->filter()
                    ->values()
                    ->all();

                $dataset->delete();
                $this->deleteStoredFiles($paths);

                return;
            }

            $path = $analysisImport->stored_path;
            $analysisImport->delete();
            $this->deleteStoredFiles([$path]);
        });

        return redirect()
            ->route('admin.imports')
            ->with('status', 'Deleted predicted rank import "' . $name . '" and removed it from the portal.');
    }

    protected function deleteStoredFiles(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            Storage::disk('local')->delete($path);
        }
    }
}
