<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Helpers\ScaffoldHelper;
use App\Services\DynamicRankImportService;
use App\Services\NeetDataImporter;

class ImportNeetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     *   --force   Truncate tables before import (default true per user request).
     *   --dry-run Only scaffold, do not import data.
     */
    protected $signature = 'neet:import {file? : Optional Excel file path to import} {--force : Truncate tables before import} {--dry-run : Only scaffold, no DB changes}';

    protected $description = 'Scans data_sheets folder, scaffolds missing migrations/models/controllers/views/routes, and imports all Excel files.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force'); // we will always truncate unless user disables via flag removal later.
        $fileArgument = $this->argument('file');
        $dataPath = base_path('data_sheets');
        $files = $fileArgument ? [$this->resolveImportFile($fileArgument)] : glob($dataPath . '/*.xlsx');
        $files = array_map(fn ($file) => realpath($file) ?: $file, $files);
        if (!$files) {
            $this->info('No Excel files found in data_sheets.');
            return 0;
        }

        if ($this->usesDbImportMode()) {
            $importer = app(DynamicRankImportService::class);

            foreach ($files as $filePath) {
                $this->info('DB importing ' . basename($filePath));
                $dataset = $importer->importFile($filePath, basename($filePath), $this->storedPathForCliImport($filePath));
                $this->info('Available at: ' . route('results.show', $dataset, false));
            }

            $this->info('DB import process completed. No Laravel source files were generated or modified.');
            return 0;
        }

        $newMigrations = [];
        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $this->info("Processing $filename");
            // Parse components
            try {
                $parts = ScaffoldHelper::parseFileName($filename);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                continue;
            }
            $state = $parts['state'];
            $year = $parts['year'];
            $descriptor = $parts['descriptor']; // may be empty
            $pageTitle = $this->pageTitleFromFilename($filename);
            $stateLabel = $this->stateLabelFromFilename($filename, $year);
            // Table names (descriptor not part of table)
            $table = "{$state}_{$year}";
            $roundTable = "{$state}_{$year}_rounds";

            // Truncation deferred until after migrations are applied.

            // Scaffold migrations if missing
            foreach ([$table => false, $roundTable => true] as $tableName => $isRound) {
                if (!ScaffoldHelper::migrationExists($tableName)) {
                    $this->createMigration($tableName, $isRound);
                    $newMigrations[] = $tableName;
                }
            }

            $modelClass = "App\\Models\\" . ucfirst($state) . $year;
            $models = [$modelClass => $table, $modelClass . 'Round' => $roundTable];
            foreach ($models as $class => $tableName) {
                if (!ScaffoldHelper::modelExists($class)) {
                    $this->createModel($class, $tableName);
                }
            }
            // Scaffold controller for descriptor (if descriptor present)
            $controllerClass = "App\\Http\\Controllers\\" . $this->controllerClassName($state, $year, $descriptor);
            if (!ScaffoldHelper::controllerExists($controllerClass)) {
                ScaffoldHelper::createController($controllerClass, $state, $year, $descriptor);
            }
            // Scaffold view
            $viewName = $state . '_' . $year . ($descriptor ? '_' . $descriptor : '');
            if (!ScaffoldHelper::viewExists($viewName)) {
                $this->createView($viewName);
            }
            $this->ensureControllerUsesGeneric($controllerClass, $table, $roundTable, $viewName, $stateLabel, $pageTitle);
            // Scaffold route
            $uri = $this->routeUri($state, $year, $descriptor);
            if (!ScaffoldHelper::routeExists($uri)) {
                $this->appendRoute($uri, $controllerClass);
            }
            $this->ensureMenuItem($year, $pageTitle, $uri);

            // If force flag, drop existing tables before migrations to avoid conflicts
            if ($force) {
                Schema::dropIfExists($table);
                Schema::dropIfExists($roundTable);
            }
            // Artisan::call('migrate', ['--force' => true]); // Skipped to avoid duplicate table creation
// $this->info(Artisan::output());
            // Reset newMigrations tracker
            $newMigrations = [];

            // Log table names
            $this->info("Target tables: {$table}, {$roundTable}");
            
            // Ensure tables exist (fallback creation) if migrations did not create them
            if (!Schema::hasTable($table)) {
                $this->info("Creating table {$table} via fallback schema...");
                Schema::create($table, function (Blueprint $tbl) {
                    $tbl->id();
                    $this->addPredictorColumns($tbl);
                    $tbl->timestamps();
                });
            }
            if (!Schema::hasTable($roundTable)) {
                $this->info("Creating round table {$roundTable} via fallback schema...");
                Schema::create($roundTable, function (Blueprint $tbl) use ($roundTable) {
                    $tbl->id();
                    $tbl->unsignedBigInteger('round_id');
                    // Generate short foreign key name with hash to ensure uniqueness
                    $fkBase = preg_replace('/[^A-Za-z0-9]/', '_', $roundTable);
                    $fkBase = substr($fkBase, 0, 30);
                                        $fkHash = substr(md5($roundTable . '_' . uniqid()), 0, 6);
                    $fkName = 'fk_' . $fkBase . '_' . $fkHash . '_rnd';
                    $tbl->foreign('round_id', $fkName)->references('id')->on('rounds');
                    $this->addPredictorColumns($tbl);
                    $tbl->integer('sort_order')->default(0);
                    $tbl->timestamps();
                });
            }
            $this->ensurePredictorColumns($table, false);
            $this->ensurePredictorColumns($roundTable, true);

            // Recreate tables with proper schema before import to avoid column mismatches
            if ($force) {
                // Drop existing tables if they exist
                Schema::dropIfExists($table);
                Schema::dropIfExists($roundTable);

                // Create main data table
                Schema::create($table, function (Blueprint $tbl) {
                    $tbl->id();
                    $this->addPredictorColumns($tbl);
                    $tbl->timestamps();
                });
                // Create round table
                Schema::create($roundTable, function (Blueprint $tbl) use ($roundTable) {
                    $tbl->id();
                    $tbl->unsignedBigInteger('round_id');
                    $fkBase = preg_replace('/[^A-Za-z0-9]/', '_', $roundTable);
                    $fkBase = substr($fkBase, 0, 30);
                    // Append short hash to ensure uniqueness across tables
                                        $fkHash = substr(md5($roundTable . '_' . uniqid()), 0, 6);
                    $fkName = 'fk_' . $fkBase . '_' . $fkHash . '_rnd';
                    $tbl->foreign('round_id', $fkName)->references('id')->on('rounds');
                    $this->addPredictorColumns($tbl);
                    $tbl->integer('sort_order')->default(0);
                    $tbl->timestamps();
                });
                $this->info("Recreated tables {$table} and {$roundTable} with proper schema.");
            }
            $this->ensureModelUsesGuarded($modelClass, $table);
            $this->ensureModelUsesGuarded($modelClass . 'Round', $roundTable);



            if (!$dryRun) {
                $importer = new NeetDataImporter($force);
                $importer->import($filePath, $state, $year, $descriptor);
            }
        }
        $this->info('Import process completed.');
        return 0;
    }

    protected function addPredictorColumns(Blueprint $tbl): void
    {
        $tbl->string('state_name')->nullable();
        $tbl->string('college_name')->nullable();
        $tbl->string('category')->nullable();
        $tbl->string('local_area')->nullable();
        $tbl->unsignedInteger('total_seats')->nullable();
        $tbl->string('quota')->nullable();
        $tbl->string('admission')->nullable();
        $tbl->unsignedBigInteger('rank')->nullable();
        $tbl->unsignedBigInteger('gen_closing_rank')->nullable();
        $tbl->unsignedBigInteger('fem_closing_rank')->nullable();
        $tbl->decimal('gen_closing_mark', 8, 2)->nullable();
        $tbl->decimal('fem_closing_mark', 8, 2)->nullable();
        $tbl->decimal('fees', 20, 2)->nullable();
        $tbl->decimal('tuition_fee', 20, 2)->nullable();
        $tbl->decimal('total_fee', 20, 2)->nullable();
        $tbl->string('seat_type')->nullable();
    }

    protected function usesDbImportMode(): bool
    {
        return config('imports.mode', 'db') === 'db'
            || ! (bool) config('imports.enable_code_generation', false);
    }

    protected function storedPathForCliImport(string $filePath): string
    {
        $basePath = rtrim(str_replace('\\', '/', base_path()), '/');
        $normalized = str_replace('\\', '/', $filePath);

        if (str_starts_with($normalized, $basePath . '/')) {
            return ltrim(substr($normalized, strlen($basePath)), '/');
        }

        return basename($filePath);
    }

    protected function ensurePredictorColumns(string $tableName, bool $isRoundTable): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $definitions = $this->predictorColumnDefinitions($isRoundTable);
        $missing = array_filter(array_keys($definitions), fn ($column) => ! Schema::hasColumn($tableName, $column));

        if (empty($missing)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definitions, $missing) {
            foreach ($missing as $column) {
                $definitions[$column]($table);
            }
        });

        $this->info('Added missing columns to ' . $tableName . ': ' . implode(', ', $missing));
    }

    protected function predictorColumnDefinitions(bool $isRoundTable): array
    {
        $columns = [
            'state_name' => fn (Blueprint $table) => $table->string('state_name')->nullable(),
            'college_name' => fn (Blueprint $table) => $table->string('college_name')->nullable(),
            'category' => fn (Blueprint $table) => $table->string('category')->nullable(),
            'local_area' => fn (Blueprint $table) => $table->string('local_area')->nullable(),
            'total_seats' => fn (Blueprint $table) => $table->unsignedInteger('total_seats')->nullable(),
            'quota' => fn (Blueprint $table) => $table->string('quota')->nullable(),
            'admission' => fn (Blueprint $table) => $table->string('admission')->nullable(),
            'rank' => fn (Blueprint $table) => $table->unsignedBigInteger('rank')->nullable(),
            'gen_closing_rank' => fn (Blueprint $table) => $table->unsignedBigInteger('gen_closing_rank')->nullable(),
            'fem_closing_rank' => fn (Blueprint $table) => $table->unsignedBigInteger('fem_closing_rank')->nullable(),
            'gen_closing_mark' => fn (Blueprint $table) => $table->decimal('gen_closing_mark', 8, 2)->nullable(),
            'fem_closing_mark' => fn (Blueprint $table) => $table->decimal('fem_closing_mark', 8, 2)->nullable(),
            'fees' => fn (Blueprint $table) => $table->decimal('fees', 20, 2)->nullable(),
            'tuition_fee' => fn (Blueprint $table) => $table->decimal('tuition_fee', 20, 2)->nullable(),
            'total_fee' => fn (Blueprint $table) => $table->decimal('total_fee', 20, 2)->nullable(),
            'seat_type' => fn (Blueprint $table) => $table->string('seat_type')->nullable(),
        ];

        if ($isRoundTable) {
            $columns['sort_order'] = fn (Blueprint $table) => $table->integer('sort_order')->default(0);
        }

        return $columns;
    }

    protected function ensureModelUsesGuarded(string $fullClass, string $table): void
    {
        $path = $this->classPath($fullClass, 'app/Models');
        if (! file_exists($path)) {
            return;
        }

        $parts = explode('\\', $fullClass);
        $className = array_pop($parts);
        $namespace = implode('\\', $parts);
        $content = "<?php\n\nnamespace {$namespace};\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$className} extends Model\n{\n    protected \$table = '{$table}';\n    protected \$guarded = [];\n}\n?>";

        file_put_contents($path, $content);
    }

    protected function ensureControllerUsesGeneric(string $controllerClass, string $table, string $roundTable, string $viewName, string $stateLabel, string $pageTitle): void
    {
        $path = $this->classPath($controllerClass, 'app/Http/Controllers');
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $parts = explode('\\', $controllerClass);
        $className = array_pop($parts);
        $namespace = implode('\\', $parts);
        $mainTable = var_export($table, true);
        $roundTableExport = var_export($roundTable, true);
        $viewNameExport = var_export($viewName, true);
        $stateLabelExport = var_export($stateLabel, true);
        $pageTitleExport = var_export($pageTitle, true);
        $content = "<?php\n\nnamespace {$namespace};\n\nclass {$className} extends GenericPredictorController\n{\n    protected string \$mainTable = {$mainTable};\n    protected string \$roundTable = {$roundTableExport};\n    protected string \$viewName = {$viewNameExport};\n    protected string \$stateLabel = {$stateLabelExport};\n    protected string \$pageTitle = {$pageTitleExport};\n}\n?>";

        file_put_contents($path, $content);
    }

    protected function classPath(string $fullClass, string $baseDirectory): string
    {
        $prefix = str_starts_with($fullClass, 'App\\') ? 'App\\' : '';
        $relative = $prefix ? substr($fullClass, strlen($prefix)) : $fullClass;
        $relative = preg_replace('#^(Models|Http\\\\Controllers)\\\\#', '', $relative);

        return base_path($baseDirectory . '/' . str_replace('\\', '/', $relative) . '.php');
    }

    protected function controllerClassName(string $state, string $year, string $descriptor): string
    {
        return ucfirst($this->codeSegment($state))
            . $year
            . ($descriptor ? ucfirst($this->codeSegment($descriptor)) : '')
            . 'Controller';
    }

    protected function routeUri(string $state, string $year, string $descriptor): string
    {
        $parts = array_filter([$state, $year, $descriptor], fn ($part) => $part !== '');
        $uri = implode('-', $parts);
        $uri = preg_replace('/[^A-Za-z0-9]+/', '-', $uri);
        $uri = strtolower(trim($uri, '-'));

        return preg_replace('/-+/', '-', $uri);
    }

    protected function pageTitleFromFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $name));

        return trim(ucwords(strtolower($name)));
    }

    protected function resolveImportFile(string $file): string
    {
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $file) || str_starts_with($file, DIRECTORY_SEPARATOR)) {
            return $file;
        }

        return base_path($file);
    }

    protected function stateLabelFromFilename(string $filename, string $year): string
    {
        $title = $this->pageTitleFromFilename($filename);
        $label = preg_replace('/\b' . preg_quote($year, '/') . '\b/i', '', $title);
        $label = preg_replace('/\b(analysis|completed|data)\b/i', '', $label);
        $label = preg_replace('/\s+/', ' ', $label);

        return trim($label) ?: $title;
    }

    protected function ensureMenuItem(string $year, string $label, string $route): void
    {
        $menusPath = config_path('menus.php');
        $menus = file_exists($menusPath) ? include $menusPath : [];
        $group = 'Results ' . $year;
        $menus[$group] = $menus[$group] ?? [];

        $exists = collect($menus[$group])->contains(fn ($item) => ($item['route'] ?? null) === $route);
        if (! $exists) {
            $menus[$group][] = ['label' => $label, 'route' => $route];
        }

        uksort($menus, function ($a, $b) {
            preg_match('/\d{4}/', $a, $aYear);
            preg_match('/\d{4}/', $b, $bYear);

            return ((int) ($bYear[0] ?? 0)) <=> ((int) ($aYear[0] ?? 0));
        });

        foreach ($menus as &$items) {
            usort($items, fn ($a, $b) => strcmp($a['label'], $b['label']));
        }

        file_put_contents($menusPath, "<?php\n\nreturn " . var_export($menus, true) . ";\n");
    }

    protected function codeSegment(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9_]+/', '_', $value);
        $value = preg_replace('/_+/', '_', $value);

        return trim($value, '_');
    }

    protected function createMigration(string $tableName, bool $isRound)
    {
        // Sanitize table name to be DB-friendly (replace hyphens and spaces with underscores)
        $sanitizedTable = preg_replace('/[^A-Za-z0-9_]/', '_', $tableName);
        // Collapse multiple underscores
        $sanitizedTable = preg_replace('/_+/', '_', $sanitizedTable);
        // Ensure no leading/trailing underscores
        $sanitizedTable = trim($sanitizedTable, '_');
        $timestamp = date('Y_m_d_His');
        // Generate a valid class name by removing underscores and capitalizing words
        $className = 'Create' . str_replace('_', '', ucwords($sanitizedTable, '_')) . 'Table';
        $fileName = base_path("database/migrations/{$timestamp}_create_{$sanitizedTable}_table.php");
        // Use custom round columns when creating round tables to limit foreign key name length
        $columns = $isRound ? $this->roundTableColumns($sanitizedTable) : $this->mainTableColumns();
        $content = "<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class {$className} extends Migration
{
    public function up()
    {
        Schema::create('{$sanitizedTable}', function (Blueprint \$table) {
            \$table->id();
            {$columns}
            \$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('{$sanitizedTable}');
    }
}
?>";
        file_put_contents($fileName, $content);
        $this->info("Created migration {$fileName}");
    }

    protected function mainTableColumns(): string
    {
        return "\$table->string('state_name')->nullable();
            \$table->string('college_name')->nullable();
            \$table->string('category')->nullable();
            \$table->string('local_area')->nullable();
            \$table->unsignedInteger('total_seats')->nullable();
            \$table->string('quota')->nullable();
            \$table->string('admission')->nullable();
            \$table->unsignedBigInteger('rank')->nullable();
            \$table->unsignedBigInteger('gen_closing_rank')->nullable();
            \$table->unsignedBigInteger('fem_closing_rank')->nullable();
            \$table->decimal('gen_closing_mark', 8, 2)->nullable();
            \$table->decimal('fem_closing_mark', 8, 2)->nullable();
            \$table->decimal('fees', 20, 2)->nullable();
            \$table->decimal('tuition_fee', 20, 2)->nullable();
            \$table->decimal('total_fee', 20, 2)->nullable();
            \$table->string('seat_type')->nullable();";
    }

    protected function roundTableColumns(string $tableName): string
    {
        // Generate a short foreign key name (max 64 characters) based on sanitized table name
        $fkBase = preg_replace('/[^A-Za-z0-9]/', '_', $tableName);
        $fkBase = substr($fkBase, 0, 30); // ensure reasonable length
        // Use hash to guarantee unique constraint names
                $fkHash = substr(md5($tableName . '_' . uniqid()), 0, 6);
        $fkName = 'fk_' . $fkBase . '_' . $fkHash . '_rnd';
        return "\$table->unsignedBigInteger('round_id');\n            \$table->foreign('round_id', '{$fkName}')->references('id')->on('rounds');\n            \$table->string('state_name')->nullable();\n            \$table->string('college_name')->nullable();\n            \$table->string('category')->nullable();\n            \$table->string('local_area')->nullable();\n            \$table->unsignedInteger('total_seats')->nullable();\n            \$table->string('quota')->nullable();\n            \$table->string('admission')->nullable();\n            \$table->unsignedBigInteger('rank')->nullable();\n            \$table->unsignedBigInteger('gen_closing_rank')->nullable();\n            \$table->unsignedBigInteger('fem_closing_rank')->nullable();\n            \$table->decimal('gen_closing_mark', 8, 2)->nullable();\n            \$table->decimal('fem_closing_mark', 8, 2)->nullable();\n            \$table->decimal('fees', 20, 2)->nullable();\n            \$table->decimal('tuition_fee', 20, 2)->nullable();\n            \$table->decimal('total_fee', 20, 2)->nullable();\n            \$table->string('seat_type')->nullable();\n            \$table->integer('sort_order')->default(0);";
    }

    protected function createModel(string $fullClass, string $table)
    {
        $parts = explode('\\', $fullClass);
        $className = array_pop($parts);
        $namespace = implode('\\', $parts);
        $filePath = base_path('app/Models/' . $className . '.php');
        $content = "<?php\n\nnamespace {$namespace};\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$className} extends Model\n{\n    protected \$table = '{$table}';\n    protected \$guarded = [];\n}\n?>";
        file_put_contents($filePath, $content);
        $this->info("Created model {$fullClass}");
    }

    /**
     * Ensure financial columns have sufficient precision.
     */
    protected function adjustColumnPrecision(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }
        Schema::table($tableName, function (Blueprint $table) {
            $table->decimal('fees', 20, 2)->nullable()->change();
            $table->decimal('tuition_fee', 20, 2)->nullable()->change();
            $table->decimal('total_fee', 20, 2)->nullable()->change();
        });
    }
    protected function createView(string $viewName)
    {
        // Copy from an existing predictor view (all_india_2025) as a template
        $templatePath = resource_path('views/all_india_2025.blade.php');
        $destPath = resource_path('views/' . $viewName . '.blade.php');
        if (file_exists($templatePath)) {
            copy($templatePath, $destPath);
            $this->info("Created view {$viewName}.blade.php from template");
        } else {
            // fallback minimal view
            $content = "@extends('layouts.app')\n\n@section('content')\n<h1>{{ ucfirst('$viewName') }} Predictor</h1>\n<p>Data will be loaded via AJAX.</p>\n<div class=\"table-responsive\"><table id=\"predictor\" class=\"table table-striped\"></table></div>\n@endsection";
            file_put_contents($destPath, $content);
            $this->info("Created minimal view {$viewName}.blade.php");
        }
    }

    protected function appendRoute(string $uri, string $controllerClass)
    {
        $routesFile = base_path('routes/web.php');
        $routeLine = "Route::get('/{$uri}', [{$controllerClass}::class, 'index'])->name('{$uri}');\n";
        file_put_contents($routesFile, "\n" . $routeLine, FILE_APPEND);
        $this->info("Appended route for {$uri}");
    }
}
?>
