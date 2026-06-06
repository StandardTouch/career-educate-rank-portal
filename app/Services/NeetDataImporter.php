<?php
namespace App\Services;

use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NeetDataImporter
{
    /**
     * If true, truncate tables before import.
     */
    protected bool $truncate;

    public function __construct(bool $truncate = true)
    {
        $this->truncate = $truncate;
    }

    /**
     * Import a single Excel file.
     *
     * @param string $filePath   Full path to the .xlsx file
     * @param string $state      State slug (e.g. "karnataka" or "all_india")
     * @param int    $year       Year (e.g. 2025)
     * @param string $descriptor Optional descriptor (e.g. "management_quota")
     */
    public function import(string $filePath, string $state, int $year, string $descriptor = ''): void
    {
        $table = "{$state}_{$year}";
        $roundTable = "{$state}_{$year}_rounds";
        $spreadsheet = IOFactory::load($filePath);

        if ($this->truncate) {
            // Truncate existing tables before import to refresh data
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                Log::info("Truncated table {$table}");
            } else {
                Log::warning("Table {$table} does not exist; cannot truncate.");
            }

            if (Schema::hasTable($roundTable)) {
                DB::table($roundTable)->truncate();
                Log::info("Truncated round table {$roundTable}");
            } else {
                Log::warning("Round table {$roundTable} does not exist; cannot truncate.");
            }
        }

        Log::info("Starting import for file {$filePath} into table {$table} and round table {$roundTable}");
        $sheetNames = $spreadsheet->getSheetNames();

        // Identify round sheets (contain the word "round" case‑insensitive)
        $roundSheets = [];
        $overallSheet = null;
        foreach ($sheetNames as $name) {
            if (preg_match('/round/i', $name)) {
                $roundSheets[] = $name;
            } else {
                // First non‑round sheet is assumed to be the overall data sheet
                if ($overallSheet === null) {
                    $overallSheet = $name;
                }
            }
        }
        // Fallback: if no explicit overall sheet, use the first sheet
        if ($overallSheet === null && count($sheetNames) > 0) {
            $overallSheet = $sheetNames[0];
        }

        // Import overall sheet
        if ($overallSheet) {
            $this->importSheet($spreadsheet->getSheetByName($overallSheet), $table);
        }

        // Ensure round slugs exist before importing round data
        foreach ($roundSheets as $roundName) {
            $slug = $this->deriveRoundSlug($roundName);
            $this->ensureRoundExists($slug);
            $this->importSheet($spreadsheet->getSheetByName($roundName), $roundTable, $slug);
        }
    }

    /** Import a worksheet into a given table.
     *  If $roundSlug is provided, the round_id column is filled.
     */
    protected function importSheet($worksheet, string $table, string $roundSlug = null): void
    {
        $rows = $worksheet->toArray(null, true, true, true);
        // Assume first row contains column headers
        $header = array_shift($rows);
        $columns = [];
        foreach ($header as $col => $title) {
            $clean = strtolower(str_replace([' ', '-'], '_', $title));
            $columns[$col] = $clean;
        }
        $existingColumns = Schema::getColumnListing($table);
        $batch = [];
        foreach ($rows as $row) {
            $record = [];
            foreach ($columns as $colIdx => $colName) {
                $value = $row[$colIdx];
                // Clean numeric values (fees, tuition_fee, total_fee)
                if (in_array($colName, ['fees', 'tuition_fee', 'total_fee'])) {
                    // Remove any non‑numeric characters (currency symbols, commas, spaces)
                    $clean = preg_replace('/[^0-9.]/', '', $value);
                    $value = $clean === '' ? null : (float) $clean;
                }
                $record[$colName] = $value;
            }
            if ($roundSlug !== null) {
                $roundId = DB::table('rounds')->where('slug', $roundSlug)->value('id');
                if (in_array('round_id', $existingColumns)) {
                    $record['round_id'] = $roundId;
                }
            }
            // Filter to existing columns only
            $record = array_intersect_key($record, array_flip($existingColumns));
            $batch[] = $record;
            if (count($batch) >= 500) {
                DB::table($table)->insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table($table)->insert($batch);
        }
    }

    /** Derive a slug like "round_1" from a sheet name. */
    protected function deriveRoundSlug(string $sheetName): string
    {
        // Extract the numeric part; fallback to sanitized name
        if (preg_match('/(\d+)/', $sheetName, $m)) {
            return 'round_' . $m[1];
        }
        return strtolower(str_replace([' ', '-'], '_', $sheetName));
    }

    /** Ensure a round entry exists in the rounds table. */
    protected function ensureRoundExists(string $slug): void
    {
        $exists = DB::table('rounds')->where('slug', $slug)->exists();
        if (! $exists) {
            DB::table('rounds')->insert([
                'slug' => $slug,
                'name' => ucwords(str_replace('_', ' ', $slug)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
?>
