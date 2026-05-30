#!/usr/bin/env php
<?php

/**
 * NEET 2025 All India Data Importer
 * ----------------------------------
 * Place this file in your Laravel project root (same folder as .env, vendor/)
 * e.g. C:\xampp\htdocs\career_educate\import_neet2025.php
 *
 * Steps:
 *   1. composer require phpoffice/phpspreadsheet
 *   2. Place ALL_INDIA_DATA_2025.xlsx in the same folder as this script
 *   3. Open CMD and run:  php import_neet2025.php
 *
 * Requirements: php_pdo_mysql, php_zip, php_gd extensions enabled in php.ini
 */

declare(strict_types=1);

// ─── Bootstrap ───────────────────────────────────────────────────────────────

define('START_TIME', microtime(true));
set_time_limit(0);
ini_set('memory_limit', '512M');

// Script lives in the Laravel project root — same directory as vendor/ and .env
$projectRoot = __DIR__;
$envFile = $projectRoot.DIRECTORY_SEPARATOR.'.env';
$xlsxFile = $projectRoot.DIRECTORY_SEPARATOR.'ALL_INDIA_DATA_2025.xlsx';

require_once $projectRoot.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// ─── Load .env ───────────────────────────────────────────────────────────────

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

// ─── DB Connection ───────────────────────────────────────────────────────────

try {
    $pdo = new PDO($dsn, $env['DB_USERNAME'] ?? '', $env['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ]);
} catch (PDOException $e) {
    exit('[ERROR] DB connection failed: '.$e->getMessage()."\n");
}

log_msg('Connected to database: '.($env['DB_DATABASE'] ?? ''));

// ─── Helpers ─────────────────────────────────────────────────────────────────

function log_msg(string $msg): void
{
    $elapsed = round(microtime(true) - START_TIME, 2);
    echo "[{$elapsed}s] {$msg}\n";
}

function cleanFee(mixed $val): ?int
{
    if ($val === null || $val === '' || (is_float($val) && is_nan($val))) {
        return null;
    }
    // Remove commas (Indian number format: "1,32,600")
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

    return (float) $val > 0 ? round((float) $val, 2) : null;
}

function cleanStr(mixed $val): string
{
    return trim((string) ($val ?? ''));
}

// ─── Fetch Round IDs ─────────────────────────────────────────────────────────

$roundMap = [];
$rows = $pdo->query('SELECT id, slug FROM rounds')->fetchAll();
foreach ($rows as $row) {
    $roundMap[$row['slug']] = (int) $row['id'];
}

if (empty($roundMap)) {
    exit("[ERROR] rounds table is empty. Run: php artisan db:seed --class=RoundsSeeder\n");
}

log_msg('Loaded rounds: '.implode(', ', array_keys($roundMap)));

// ─── Sheet → Round slug mapping ──────────────────────────────────────────────

// Keys must match EXACT sheet names in the xlsx
$sheetRoundMap = [
    'ROUND 1' => 'round_1',
    'ROUND 2' => 'round_2',
    'MOPUP Round' => 'mopup_round',
    'Stray Round' => 'stray_round',
    'Special Stray Round' => 'special_stray_round',
];

// ─── Verify file ─────────────────────────────────────────────────────────────

if (! file_exists($xlsxFile)) {
    exit("[ERROR] XLSX not found at: $xlsxFile\n");
}

log_msg('Loading spreadsheet (this may take a moment)...');
$spreadsheet = IOFactory::load($xlsxFile);
log_msg('Spreadsheet loaded.');

// ─── Truncate existing data ───────────────────────────────────────────────────

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE all_india_2025');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
log_msg('Truncated all_india_2025 table.');

// ─── Prepare insert statement ─────────────────────────────────────────────────

$insertSql = '
    INSERT INTO all_india_2025
        (round_id, state_name, college_name, category, local_area, total_seats,
         gen_closing_rank, fem_closing_rank, gen_closing_mark, fem_closing_mark,
         tuition_fee, created_at, updated_at)
    VALUES
        (:round_id, :state_name, :college_name, :category, :local_area, :total_seats,
         :gen_closing_rank, :fem_closing_rank, :gen_closing_mark, :fem_closing_mark,
         :tuition_fee, :created_at, :updated_at)
';

$stmt = $pdo->prepare($insertSql);
$now = date('Y-m-d H:i:s');

// ─── Process each sheet ───────────────────────────────────────────────────────

const BATCH_SIZE = 500;
$totalInserted = 0;

foreach ($sheetRoundMap as $sheetName => $roundSlug) {

    if (! isset($roundMap[$roundSlug])) {
        log_msg("  [SKIP] Round slug '{$roundSlug}' not found in DB. Skipping sheet '{$sheetName}'.");

        continue;
    }

    $sheet = $spreadsheet->getSheetByName($sheetName);
    if ($sheet === null) {
        log_msg("  [SKIP] Sheet '{$sheetName}' not found in xlsx.");

        continue;
    }

    $roundId = $roundMap[$roundSlug];
    $rows = $sheet->toArray(null, true, true, false);
    $headers = array_shift($rows); // Remove header row

    // Detect column positions dynamically by normalising header names
    // This handles the fact that each sheet uses slightly different column names
    // (e.g. "GEN Round 1 Rank" vs "GEN MOPUP Round Rank")
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

    $sheetInserted = 0;
    $pdo->beginTransaction();

    foreach ($rows as $lineNum => $row) {

        // Skip completely empty rows
        $rowValues = array_filter($row, fn ($v) => $v !== null && $v !== '');
        if (empty($rowValues)) {
            continue;
        }

        $get = fn (string $key) => isset($colIndex[$key]) ? $row[$colIndex[$key]] : null;

        $stmt->execute([
            ':round_id' => $roundId,
            ':state_name' => cleanStr($get('state')),
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

        // Commit in batches to avoid large transaction overhead
        if ($sheetInserted % BATCH_SIZE === 0) {
            $pdo->commit();
            $pdo->beginTransaction();
            log_msg("  [{$sheetName}] {$sheetInserted} rows inserted...");
        }
    }

    // Commit remaining rows
    $pdo->commit();
    $totalInserted += $sheetInserted;
    log_msg("  [{$sheetName}] Done. {$sheetInserted} rows inserted. (round_id={$roundId})");
}

// ─── Summary ─────────────────────────────────────────────────────────────────

$elapsed = round(microtime(true) - START_TIME, 2);
log_msg('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
log_msg("Import complete. Total rows: {$totalInserted} in {$elapsed}s");

// Verify count
$count = $pdo->query('SELECT COUNT(*) as c FROM all_india_2025')->fetch()['c'];
log_msg("DB verification: all_india_2025 has {$count} rows.");
log_msg('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
