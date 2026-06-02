#!/usr/bin/env php
<?php

/**
 * NEET 2025 All India Data Importer
 * ----------------------------------
 * Place this file in your Laravel project root (same folder as .env, vendor/)
 * e.g. C:\xampp\htdocs\career_educate\import_neet2025.php
 *
 * TABLE 1 → karnataka_2024
 *   Source : "ALL INDIA Over all" sheet (2840 rows)
 *   Columns: state_name, college_name, category, local_area, total_seats,
 *            gen_closing_rank, fem_closing_rank, gen_closing_mark,
 *            fem_closing_mark, tuition_fee
 *   No round_id column.
 *
 * TABLE 2 → karnataka_2024_rounds
 *   Source : ROUND 1, ROUND 2, MOPUP Round, Stray Round, Special Stray Round
 *   Columns: same 10 columns + round_id (FK to rounds table)
 *
 * Steps:
 *   1. composer require phpoffice/phpspreadsheet
 *   2. Place KARNATAKA_DATA_2025.xlsx in the same folder as this script
 *   3. Run migrations + RoundsSeeder first
 *   4. Open CMD and run: php import_neet2025.php
 */

declare(strict_types=1);

// ─── Bootstrap ───────────────────────────────────────────────────────────────

define('START_TIME', microtime(true));
set_time_limit(0);
ini_set('memory_limit', '512M');

$projectRoot = __DIR__;
$envFile = $projectRoot.DIRECTORY_SEPARATOR.'.env';
$xlsxFile = $projectRoot.DIRECTORY_SEPARATOR.'KARNATAKA_DATA_2024.xlsx';

require_once $projectRoot.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function log_msg(string $msg): void
{
    $elapsed = round(microtime(true) - START_TIME, 2);
    echo "[{$elapsed}s] {$msg}\n";
}

function loadEnv(string $path): array
{
    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
        }
    }

    return $env;
}

function cleanFee(mixed $val): ?int
{
    if ($val === null || $val === '' || (is_float($val) && is_nan($val))) {
        return null;
    }
    $clean = preg_replace('/[^\d]/', '', (string) $val);

    return $clean !== '' ? (int) $clean : null;
}

function cleanRank(mixed $val): ?int
{
    if ($val === null || $val === '' || (is_float($val) && is_nan($val))) {
        return null;
    }
    $int = (int) round((float) $val);

    return $int > 0 ? $int : null;
}

function cleanMark(mixed $val): ?float
{
    if ($val === null || $val === '' || (is_float($val) && is_nan($val))) {
        return null;
    }
    $f = round((float) $val, 2);

    return $f > 0 ? $f : null;
}

function cleanStr(mixed $val): string
{
    return trim((string) ($val ?? ''));
}

/**
 * Detect column positions dynamically from header row.
 * Works across all sheets even when header names differ per round.
 */
function detectColumns(array $headers): array
{
    $colIndex = [];
    foreach ($headers as $i => $header) {
        $h = strtolower(trim((string) $header));
        if (str_contains($h, 'state')) {
            $colIndex['state'] = $i;
        } elseif (str_contains($h, 'college')) {
            $colIndex['college'] = $i;
        } elseif (str_contains($h, 'category')) {
            $colIndex['category'] = $i;
        } elseif (str_contains($h, 'local')) {
            $colIndex['local_area'] = $i;
        } elseif (str_contains($h, 'seat')) {
            $colIndex['seats'] = $i;
        } elseif (str_contains($h, 'fem') && str_contains($h, 'rank')) {
            $colIndex['fem_rank'] = $i;
        } elseif (str_contains($h, 'gen') && str_contains($h, 'rank')) {
            $colIndex['gen_rank'] = $i;
        } elseif (str_contains($h, 'fem') && str_contains($h, 'mark')) {
            $colIndex['fem_mark'] = $i;
        } elseif (str_contains($h, 'gen') && str_contains($h, 'mark')) {
            $colIndex['gen_mark'] = $i;
        } elseif (str_contains($h, 'fee')) {
            $colIndex['fee'] = $i;
        }
    }

    return $colIndex;
}

// ─── Load .env & connect ──────────────────────────────────────────────────────

if (! file_exists($envFile)) {
    exit("[ERROR] .env not found at: $envFile\n");
}

$env = loadEnv($envFile);

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_PORT'] ?? '3306',
    $env['DB_DATABASE'] ?? ''
);

try {
    $pdo = new PDO($dsn, $env['DB_USERNAME'] ?? '', $env['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ]);
} catch (PDOException $e) {
    exit('[ERROR] DB connection failed: '.$e->getMessage()."\n");
}

log_msg('Connected to: '.($env['DB_DATABASE'] ?? ''));

// ─── Load round map from DB ───────────────────────────────────────────────────

$roundMap = [];
foreach ($pdo->query('SELECT id, slug FROM rounds')->fetchAll() as $row) {
    $roundMap[$row['slug']] = (int) $row['id'];
}

if (empty($roundMap)) {
    exit("[ERROR] rounds table is empty. Run: php artisan db:seed --class=RoundsSeeder\n");
}

log_msg('Rounds loaded: '.implode(', ', array_keys($roundMap)));

// ─── Verify XLSX ──────────────────────────────────────────────────────────────

if (! file_exists($xlsxFile)) {
    exit("[ERROR] XLSX not found at: $xlsxFile\n");
}

log_msg('Loading spreadsheet...');
$spreadsheet = IOFactory::load($xlsxFile);
log_msg('Spreadsheet loaded.');

const BATCH_SIZE = 500;
$now = date('Y-m-d H:i:s');

// ═════════════════════════════════════════════════════════════════════════════
// IMPORT 1 — karnataka_2024
// Source: "ALL INDIA Over all" sheet
// No round_id column
// ═════════════════════════════════════════════════════════════════════════════

log_msg('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
log_msg('IMPORT 1: karnataka_2024 (Overall sheet)');
log_msg('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

$overallSheet = $spreadsheet->getSheetByName('Karnataka Medical Colleges');
if ($overallSheet === null) {
    exit("[ERROR] Sheet 'Karnataka Medical Colleges' not found in xlsx.\n");
}

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE karnataka_2024');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
log_msg('Truncated karnataka_2024');

$stmtOverall = $pdo->prepare('
    INSERT INTO karnataka_2024
        (college_name, category, local_area, total_seats,
         gen_closing_rank, fem_closing_rank, gen_closing_mark, fem_closing_mark,
         tuition_fee, created_at, updated_at)
    VALUES
        (:college_name, :category, :local_area, :total_seats,
         :gen_closing_rank, :fem_closing_rank, :gen_closing_mark, :fem_closing_mark,
         :tuition_fee, :created_at, :updated_at)
');

$overallRows = $overallSheet->toArray(null, true, true, false);
$colIndex = detectColumns(array_shift($overallRows));

$inserted = 0;
$pdo->beginTransaction();

foreach ($overallRows as $row) {
    if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
        continue;
    }

    $get = fn (string $key) => isset($colIndex[$key]) ? $row[$colIndex[$key]] : null;

    $stmtOverall->execute([
        // ':state_name' => cleanStr($get('state')),
        ':college_name' => cleanStr($get('college')),
        ':category' => cleanStr($get('category')),
        ':local_area' => cleanStr($get('local_area')),
        ':total_seats' => (int) ($get('seats') ?? 0),
        ':gen_closing_rank' => cleanRank($get('gen_rank')),
        ':fem_closing_rank' => cleanRank($get('fem_rank')),
        ':gen_closing_mark' => cleanMark($get('gen_mark')),
        ':fem_closing_mark' => cleanMark($get('fem_mark')),
        ':tuition_fee' => cleanFee($get('fee')),
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $inserted++;

    if ($inserted % BATCH_SIZE === 0) {
        $pdo->commit();
        $pdo->beginTransaction();
        log_msg("  [Overall] {$inserted} rows inserted...");
    }
}

$pdo->commit();
log_msg("  [Overall] Done. {$inserted} rows inserted into karnataka_2024.");

// Verify
$count = $pdo->query('SELECT COUNT(*) as c FROM karnataka_2024')->fetch()['c'];
log_msg("  DB check: karnataka_2024 has {$count} rows.");

// ═════════════════════════════════════════════════════════════════════════════
// IMPORT 2 — karnataka_2024_rounds
// Source: ROUND 1, ROUND 2, MOPUP Round, Stray Round, Special Stray Round
// Includes round_id column
// ═════════════════════════════════════════════════════════════════════════════

log_msg('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
log_msg('IMPORT 2: karnataka_2024_rounds (5 round sheets)');
log_msg('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

// Sheet name → round slug mapping
$sheetRoundMap = [
    'ROUND 1' => 'round_1',
    'ROUND 2' => 'round_2',
    'MOPUP Round' => 'mopup_round',
    'Stray Round' => 'stray_round',
    'Special Stray Round' => 'special_stray_round',
    'Special Stray 2 Round' => 'special_stray_2_round',

];

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE karnataka_2024_rounds');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
log_msg('Truncated karnataka_2024_rounds.');

$stmtRound = $pdo->prepare('
    INSERT INTO karnataka_2024_rounds
        (round_id, college_name, category, local_area, total_seats,
         gen_closing_rank, fem_closing_rank, gen_closing_mark, fem_closing_mark,
         tuition_fee, created_at, updated_at)
    VALUES
        (:round_id, :college_name, :category, :local_area, :total_seats,
         :gen_closing_rank, :fem_closing_rank, :gen_closing_mark, :fem_closing_mark,
         :tuition_fee, :created_at, :updated_at)
');

$totalRoundRows = 0;

foreach ($sheetRoundMap as $sheetName => $roundSlug) {

    if (! isset($roundMap[$roundSlug])) {
        log_msg("  [SKIP] Slug '{$roundSlug}' not in DB. Skipping '{$sheetName}'.");

        continue;
    }

    $sheet = $spreadsheet->getSheetByName($sheetName);
    if ($sheet === null) {
        log_msg("  [SKIP] Sheet '{$sheetName}' not found in xlsx.");

        continue;
    }

    $roundId = $roundMap[$roundSlug];
    $sheetRows = $sheet->toArray(null, true, true, false);
    $colIndex = detectColumns(array_shift($sheetRows));

    $sheetInserted = 0;
    $pdo->beginTransaction();

    foreach ($sheetRows as $row) {
        if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
            continue;
        }

        $get = fn (string $key) => isset($colIndex[$key]) ? $row[$colIndex[$key]] : null;

        $stmtRound->execute([
            ':round_id' => $roundId,
            // ':state_name' => cleanStr($get('state')),
            ':college_name' => cleanStr($get('college')),
            ':category' => cleanStr($get('category')),
            ':local_area' => cleanStr($get('local_area')),
            ':total_seats' => (int) ($get('seats') ?? 0),
            ':gen_closing_rank' => cleanRank($get('gen_rank')),
            ':fem_closing_rank' => cleanRank($get('fem_rank')),
            ':gen_closing_mark' => cleanMark($get('gen_mark')),
            ':fem_closing_mark' => cleanMark($get('fem_mark')),
            ':tuition_fee' => cleanFee($get('fee')),
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $sheetInserted++;

        if ($sheetInserted % BATCH_SIZE === 0) {
            $pdo->commit();
            $pdo->beginTransaction();
            log_msg("  [{$sheetName}] {$sheetInserted} rows inserted...");
        }
    }

    $pdo->commit();
    $totalRoundRows += $sheetInserted;
    log_msg("  [{$sheetName}] Done. {$sheetInserted} rows. (round_id={$roundId})");
}

// Verify
$count = $pdo->query('SELECT COUNT(*) as c FROM karnataka_2024_rounds')->fetch()['c'];
log_msg("  DB check: karnataka_2024_rounds has {$count} rows.");

// ─── Final Summary ────────────────────────────────────────────────────────────

$elapsed = round(microtime(true) - START_TIME, 2);
log_msg('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
log_msg("All done in {$elapsed}s");
log_msg("  karnataka_2024        → {$inserted} rows (Overall sheet)");
log_msg("  karnataka_2024_rounds → {$totalRoundRows} rows (5 round sheets)");
log_msg('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
