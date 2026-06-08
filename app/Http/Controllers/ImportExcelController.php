<?php

namespace App\Http\Controllers;

use App\Helpers\ScaffoldHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ImportExcelController extends Controller
{
    public function create()
    {
        return view('import-excel');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx', 'max:51200'],
        ]);

        $file = $validated['excel_file'];
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

    protected function safeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9 _-]+/', '', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        return ($name !== '' ? $name : 'neet-import-' . now()->format('YmdHis')) . '.' . strtolower($extension);
    }

    protected function routeUri(string $state, string $year, string $descriptor): string
    {
        $parts = array_filter([$state, $year, $descriptor], fn ($part) => $part !== '');
        $uri = implode('-', $parts);
        $uri = preg_replace('/[^A-Za-z0-9]+/', '-', $uri);
        $uri = strtolower(trim($uri, '-'));

        return preg_replace('/-+/', '-', $uri);
    }
}
