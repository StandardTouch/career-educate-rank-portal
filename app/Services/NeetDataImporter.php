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
            $clean = $this->normalizeHeader((string) $title);
            $columns[$col] = $clean;
        }
        $existingColumns = Schema::getColumnListing($table);
        $roundId = $roundSlug !== null ? DB::table('rounds')->where('slug', $roundSlug)->value('id') : null;
        $existingIds = $this->truncate ? [] : $this->existingRowIds($table, $roundId);
        $batch = [];
        $rowIndex = 0;
        foreach ($rows as $row) {
            $record = [];
            foreach ($columns as $colIdx => $colName) {
                $value = $row[$colIdx];
                // Clean numeric values (fees, tuition_fee, total_fee)
                if (in_array($colName, ['fees', 'tuition_fee', 'total_fee', 'gen_closing_mark', 'fem_closing_mark'], true)) {
                    // Remove any non‑numeric characters (currency symbols, commas, spaces)
                    $value = $this->cleanDecimal($value);
                }
                if (in_array($colName, ['rank', 'gen_closing_rank', 'fem_closing_rank', 'total_seats', 'sort_order'], true)) {
                    $value = $this->cleanInteger($value);
                }
                $record[$colName] = $value;
            }
            $record = $this->fixRankMarkMisplacements($record);
            if ($roundSlug !== null) {
                if (in_array('round_id', $existingColumns)) {
                    $record['round_id'] = $roundId;
                }
            }
            // Filter to existing columns only
            $record = array_intersect_key($record, array_flip($existingColumns));

            if (isset($existingIds[$rowIndex])) {
                DB::table($table)->where('id', $existingIds[$rowIndex])->update($record);
                $rowIndex++;
                continue;
            }

            $batch[] = $record;
            $rowIndex++;
            if (count($batch) >= 500) {
                DB::table($table)->insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table($table)->insert($batch);
        }
    }

    protected function existingRowIds(string $table, ?int $roundId): array
    {
        $query = DB::table($table)->orderBy('id');

        if ($roundId !== null && Schema::hasColumn($table, 'round_id')) {
            $query->where('round_id', $roundId);
        }

        return $query->pluck('id')->all();
    }

    protected function normalizeHeader(string $title): string
    {
        $normalized = strtolower(trim($title));
        $normalized = preg_replace('/\s*\([^)]*\)/', '', $normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);
        $normalized = trim($normalized, '_');

        return match ($normalized) {
            'state', 'state_name' => 'state_name',
            'college', 'college_name' => 'college_name',
            'total_seats', 'total_seat', 'seats' => 'total_seats',
            'gen_closing_rank', 'general_closing_rank', 'closing_rank' => 'gen_closing_rank',
            'fem_closing_rank', 'female_closing_rank' => 'fem_closing_rank',
            'gen_closing_mark', 'general_closing_mark', 'closing_mark' => 'gen_closing_mark',
            'fem_closing_mark', 'female_closing_mark' => 'fem_closing_mark',
            default => $this->normalizeRoundHeader($normalized),
        };
    }

    protected function normalizeRoundHeader(string $normalized): string
    {
        if (preg_match('/^(gen|general)_.+_rank$/', $normalized)) {
            return 'gen_closing_rank';
        }

        if (preg_match('/^(fem|female)_.+_rank$/', $normalized)) {
            return 'fem_closing_rank';
        }

        if (preg_match('/^(gen|general)_.+_mark$/', $normalized)) {
            return 'gen_closing_mark';
        }

        if (preg_match('/^(fem|female)_.+_mark$/', $normalized)) {
            return 'fem_closing_mark';
        }

        return $normalized;
    }

    protected function fixRankMarkMisplacements(array $record): array
    {
        foreach ([
            'gen_closing_mark' => 'gen_closing_rank',
            'fem_closing_mark' => 'fem_closing_rank',
        ] as $markColumn => $rankColumn) {
            if (
                array_key_exists($markColumn, $record)
                && ($record[$markColumn] !== null && $record[$markColumn] > 720)
                && (! array_key_exists($rankColumn, $record) || $record[$rankColumn] === null)
            ) {
                $record[$rankColumn] = (int) $record[$markColumn];
                $record[$markColumn] = null;
            }
        }

        return $record;
    }

    protected function cleanDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9.]/', '', (string) $value);

        return $clean === '' ? null : (float) $clean;
    }

    protected function cleanInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $value);

        return $clean === '' ? null : (int) $clean;
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
                'sort_order' => $this->cleanInteger($slug) ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
?>
