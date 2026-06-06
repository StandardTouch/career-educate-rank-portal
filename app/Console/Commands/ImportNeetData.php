<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Helpers\ScaffoldHelper;
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
    protected $signature = 'neet:import {--force : Truncate tables before import} {--dry-run : Only scaffold, no DB changes}';

    protected $description = 'Scans data_sheets folder, scaffolds missing migrations/models/controllers/views/routes, and imports all Excel files.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force'); // we will always truncate unless user disables via flag removal later.
        $dataPath = base_path('data_sheets');
        $files = glob($dataPath . '/*.xlsx');
        if (!$files) {
            $this->info('No Excel files found in data_sheets.');
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
            $controllerClass = "App\\Http\\Controllers\\" . ucfirst($state) . $year . ($descriptor ? ucfirst($descriptor) : '') . 'Controller';
            if (!ScaffoldHelper::controllerExists($controllerClass)) {
                ScaffoldHelper::createController($controllerClass, $state, $year, $descriptor);
            }
            // Scaffold view
            $viewName = $state . '_' . $year . ($descriptor ? '_' . $descriptor : '');
            if (!ScaffoldHelper::viewExists($viewName)) {
                $this->createView($viewName);
            }
            // Scaffold route
            $uri = $state . ($year ? "-{$year}" : '') . ($descriptor ? "-{$descriptor}" : '');
            if (!ScaffoldHelper::routeExists($uri)) {
                $this->appendRoute($uri, $controllerClass);
            }

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
                    $tbl->string('college_name')->nullable();
                    $tbl->string('category')->nullable();
                    $tbl->string('local_area')->nullable();
                    $tbl->string('quota')->nullable();
                    $tbl->string('admission')->nullable();
                    $tbl->integer('rank')->nullable();
                    $tbl->decimal('fees', 10, 2)->nullable();
                    $tbl->decimal('tuition_fee', 10, 2)->nullable();
                    $tbl->decimal('total_fee', 10, 2)->nullable();
                    $tbl->string('seat_type')->nullable();
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
                    $tbl->string('college_name')->nullable();
                    $tbl->string('category')->nullable();
                    $tbl->string('local_area')->nullable();
                    $tbl->string('quota')->nullable();
                    $tbl->string('admission')->nullable();
                    $tbl->integer('rank')->nullable();
                    $tbl->decimal('fees', 10, 2)->nullable();
                    $tbl->decimal('tuition_fee', 10, 2)->nullable();
                    $tbl->decimal('total_fee', 10, 2)->nullable();
                    $tbl->string('seat_type')->nullable();
                    $tbl->integer('sort_order')->default(0);
                    $tbl->timestamps();
                });
            }

            // Recreate tables with proper schema before import to avoid column mismatches
            if ($force) {
                // Drop existing tables if they exist
                Schema::dropIfExists($table);
                Schema::dropIfExists($roundTable);

                // Create main data table
                Schema::create($table, function (Blueprint $tbl) {
                    $tbl->id();
                    $tbl->string('college_name')->nullable();
                    $tbl->string('category')->nullable();
                    $tbl->string('local_area')->nullable();
                    $tbl->string('quota')->nullable();
                    $tbl->string('admission')->nullable();
                    $tbl->integer('rank')->nullable();
                    $tbl->decimal('fees', 20, 2)->nullable();
                    $tbl->decimal('tuition_fee', 20, 2)->nullable();
                    $tbl->decimal('total_fee', 20, 2)->nullable();
                    $tbl->string('seat_type')->nullable();
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
                    $tbl->string('college_name')->nullable();
                    $tbl->string('category')->nullable();
                    $tbl->string('local_area')->nullable();
                    $tbl->string('quota')->nullable();
                    $tbl->string('admission')->nullable();
                    $tbl->integer('rank')->nullable();
                    $tbl->decimal('fees', 20, 2)->nullable();
                    $tbl->decimal('tuition_fee', 20, 2)->nullable();
                    $tbl->decimal('total_fee', 20, 2)->nullable();
                    $tbl->string('seat_type')->nullable();
                    $tbl->integer('sort_order')->default(0);
                    $tbl->timestamps();
                });
                $this->info("Recreated tables {$table} and {$roundTable} with proper schema.");
            }



            if (!$dryRun) {
                $importer = new NeetDataImporter($force);
                $importer->import($filePath, $state, $year, $descriptor);
            }
        }
        $this->info('Import process completed.');
        return 0;
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
        // Mirrors existing karnataka_2025 schema (simplified for demo)
        return "\$table->string('college_name')->nullable();
            \$table->string('category')->nullable();
            \$table->string('local_area')->nullable();
            \$table->string('quota')->nullable();
            \$table->string('admission')->nullable();
            \$table->integer('rank')->nullable();
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
        return "\$table->unsignedBigInteger('round_id');\n            \$table->foreign('round_id', '{$fkName}')->references('id')->on('rounds');\n            \$table->string('college_name')->nullable();\n            \$table->string('category')->nullable();\n            \$table->string('local_area')->nullable();\n            \$table->string('quota')->nullable();\n            \$table->string('admission')->nullable();\n            \$table->integer('rank')->nullable();\n            \$table->decimal('fees', 20, 2)->nullable();\n            \$table->decimal('tuition_fee', 20, 2)->nullable();\n            \$table->decimal('total_fee', 20, 2)->nullable();\n            \$table->string('seat_type')->nullable();\n            \$table->integer('sort_order')->default(0);";
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
        $routeLine = "Route::get('/{$uri}', [{$controllerClass}::class, 'index']);\n";
        file_put_contents($routesFile, "\n" . $routeLine, FILE_APPEND);
        $this->info("Appended route for {$uri}");
    }
}
?>
