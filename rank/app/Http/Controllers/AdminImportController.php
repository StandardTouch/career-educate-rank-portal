<?php

namespace App\Http\Controllers;

use App\Models\AnalysisImport;
use App\Models\Import;
use App\Models\MenuFolder;
use App\Models\NotificationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminImportController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $resultImports = Import::with('dataset')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('original_filename', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('total_rows', 'like', "%{$search}%")
                        ->orWhereDate('created_at', $search)
                        ->orWhereHas('dataset', function ($datasetQuery) use ($search): void {
                            $datasetQuery->where('title', 'like', "%{$search}%")
                                ->orWhere('course', 'like', "%{$search}%")
                                ->orWhere('year', 'like', "%{$search}%")
                                ->orWhere('state', 'like', "%{$search}%")
                                ->orWhere('quota', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(15, ['*'], 'result_page')
            ->withQueryString();

        $predictedRankImports = AnalysisImport::with('analysisDataset')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('original_filename', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('total_rows', 'like', "%{$search}%")
                        ->orWhereDate('created_at', $search)
                        ->orWhereHas('analysisDataset', function ($datasetQuery) use ($search): void {
                            $datasetQuery->where('title', 'like', "%{$search}%")
                                ->orWhere('course', 'like', "%{$search}%")
                                ->orWhere('year', 'like', "%{$search}%")
                                ->orWhere('state', 'like', "%{$search}%")
                                ->orWhere('quota', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(15, ['*'], 'predicted_page')
            ->withQueryString();

        $notificationDocuments = NotificationDocument::with('menuFolder.parent.parent')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('dropdown_name', 'like', "%{$search}%")
                        ->orWhere('original_filename', 'like', "%{$search}%")
                        ->orWhereDate('created_at', $search)
                        ->orWhereHas('menuFolder', function ($folderQuery) use ($search): void {
                            $folderQuery->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(15, ['*'], 'notification_page')
            ->withQueryString();

        $folderOptions = MenuFolder::with('parent.parent')
            ->orderBy('depth')
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (MenuFolder $folder) => [
                'id' => $folder->id,
                'path' => $folder->pathTitle(),
                'depth' => $folder->depth,
            ])
            ->values()
            ->all();

        return view('admin.imports.index', compact('resultImports', 'predictedRankImports', 'notificationDocuments', 'folderOptions', 'search'));
    }

    public function updateResult(Request $request, Import $import): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
        ]);

        if ($import->dataset) {
            $import->dataset->update(['title' => trim($validated['title'])]);
        }

        return redirect()
            ->route('admin.imports')
            ->with('status', 'Updated result import title.');
    }

    public function updatePredictedRank(Request $request, AnalysisImport $analysisImport): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
        ]);

        if ($analysisImport->analysisDataset) {
            $analysisImport->analysisDataset->update(['title' => trim($validated['title'])]);
        }

        return redirect()
            ->route('admin.imports')
            ->with('status', 'Updated predicted rank import title.');
    }

    public function updateNotification(Request $request, NotificationDocument $notificationDocument): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'menu_folder_id' => ['required', 'integer', 'exists:menu_folders,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $folder = MenuFolder::findOrFail((int) $validated['menu_folder_id']);

        $notificationDocument->update([
            'title' => trim($validated['title']),
            'menu_folder_id' => $folder->id,
            'dropdown_name' => $folder->title,
            'sort_order' => $validated['sort_order'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('admin.imports')
            ->with('status', 'Updated notification PDF.');
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

    public function destroyNotification(NotificationDocument $notificationDocument): RedirectResponse
    {
        $name = $notificationDocument->title;
        $path = $notificationDocument->stored_path;

        $notificationDocument->delete();
        Storage::disk('public')->delete($path);

        return redirect()
            ->route('admin.imports')
            ->with('status', 'Deleted notification PDF "' . $name . '" and removed it from the Notifications dropdown.');
    }

    protected function deleteStoredFiles(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            Storage::disk('local')->delete($path);
        }
    }
}
