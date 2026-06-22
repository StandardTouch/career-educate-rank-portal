<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ResetDynamicRankData extends Command
{
    protected $signature = 'rank:reset-dynamic-data
        {--force : Run without interactive confirmation}
        {--delete-generated-files : Delete old code-generation artifacts after showing the list}
        {--delete-generated-tables : Drop old generated rank tables after showing the list}';

    protected $description = 'Reset DB-driven rank import data and optionally remove old generated rank portal artifacts.';

    /**
     * These files/classes are hand-written application code or the new DB-driven import system.
     * The cleanup must never remove whole directories or protected files, only confirmed old generated artifacts.
     */
    protected array $protectedControllerFiles = [
        'AdminDashboardController.php',
        'AuthController.php',
        'BasePredictorController.php',
        'Controller.php',
        'GenericPredictorController.php',
        'ImportExcelController.php',
        'ResultController.php',
    ];

    protected array $protectedModelFiles = [
        'Dataset.php',
        'Import.php',
        'ImportSheet.php',
        'RankRecord.php',
        'Round.php',
        'User.php',
    ];

    protected array $protectedViewFiles = [
        'admin/dashboard.blade.php',
        'auth/login.blade.php',
        'auth/register-details.blade.php',
        'auth/register-phone.blade.php',
        'auth/verify-mobile.blade.php',
        'dashboard.blade.php',
        'home.blade.php',
        'import-excel.blade.php',
        'partials/results-header.blade.php',
        'results/show.blade.php',
        'welcome.blade.php',
    ];

    protected array $protectedMigrationFiles = [
        '0001_01_01_000000_create_users_table.php',
        '0001_01_01_000001_create_cache_table.php',
        '0001_01_01_000002_create_jobs_table.php',
        '2025_01_01_000001_create_rounds_table.php',
        '2026_06_10_000000_add_is_admin_to_users_table.php',
        '2026_06_13_000000_add_mobile_verification_to_users_table.php',
        '2026_06_13_010000_create_dynamic_import_tables.php',
    ];

    protected array $protectedTables = [
        'cache',
        'cache_locks',
        'datasets',
        'failed_jobs',
        'import_sheets',
        'imports',
        'job_batches',
        'jobs',
        'migrations',
        'password_reset_tokens',
        'rank_records',
        'rounds',
        'sessions',
        'users',
    ];

    public function handle(): int
    {
        $deleteFiles = (bool) $this->option('delete-generated-files');
        $deleteTables = (bool) $this->option('delete-generated-tables');
        $force = (bool) $this->option('force');

        $files = $deleteFiles ? $this->generatedFiles() : [];
        $tables = $deleteTables ? $this->generatedTables() : [];

        $this->printPlan($files, $tables);

        if (! $force && ! $this->confirm('Proceed with this reset?', false)) {
            $this->info('Reset cancelled.');
            return self::SUCCESS;
        }

        if ($deleteFiles) {
            $this->cleanGeneratedRoutes($files);
            $this->cleanGeneratedMenus();
        }

        $dbCounts = $this->deleteDynamicData();
        $deletedFiles = $deleteFiles ? $this->deleteFiles($files) : 0;
        $droppedTables = $deleteTables ? $this->dropTables($tables) : 0;

        $this->newLine();
        $this->info('Reset completed.');
        $this->line('DB-driven rows deleted: ' . array_sum($dbCounts));
        foreach ($dbCounts as $table => $count) {
            $this->line("  {$table}: {$count}");
        }
        $this->line("Files deleted: {$deletedFiles}");
        $this->line("Old generated tables dropped: {$droppedTables}");

        return self::SUCCESS;
    }

    protected function printPlan(array $files, array $tables): void
    {
        $this->warn('Dry-run plan');
        $this->line('Default reset always deletes DB-driven import data only: rank_records, import_sheets, imports, dataset-scoped rounds, datasets.');

        if ($this->option('delete-generated-files')) {
            $this->newLine();
            $this->warn('Generated files selected for deletion: ' . count($files));
            foreach ($files as $file) {
                $this->line('  - ' . $this->relativePath($file));
            }
        } else {
            $this->line('Generated files: not selected. Pass --delete-generated-files to include them.');
        }

        if ($this->option('delete-generated-tables')) {
            $this->newLine();
            $this->warn('Old generated tables selected for drop: ' . count($tables));
            foreach ($tables as $table) {
                $this->line('  - ' . $table);
            }
        } else {
            $this->line('Old generated tables: not selected. Pass --delete-generated-tables to include them.');
        }
    }

    protected function deleteDynamicData(): array
    {
        $counts = [];

        DB::transaction(function () use (&$counts) {
            foreach (['rank_records', 'import_sheets', 'imports'] as $table) {
                $counts[$table] = $this->deleteFromTable($table);
            }

            $counts['dataset_scoped_rounds'] = Schema::hasTable('rounds') && Schema::hasColumn('rounds', 'dataset_id')
                ? DB::table('rounds')->whereNotNull('dataset_id')->delete()
                : 0;

            $counts['datasets'] = $this->deleteFromTable('datasets');
        });

        return $counts;
    }

    protected function deleteFromTable(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->delete();
    }

    protected function generatedFiles(): array
    {
        $files = array_merge(
            $this->generatedControllerFiles(),
            $this->generatedModelFiles(),
            $this->generatedViewFiles(),
            $this->generatedExcelFiles(),
            $this->generatedMigrationFiles()
        );

        $files = array_values(array_unique(array_filter($files, fn ($file) => is_file($file))));
        sort($files);

        return $files;
    }

    protected function generatedControllerFiles(): array
    {
        $files = glob(app_path('Http/Controllers/*.php')) ?: [];

        return array_values(array_filter($files, function (string $file): bool {
            if (in_array(basename($file), $this->protectedControllerFiles, true)) {
                return false;
            }

            $content = (string) file_get_contents($file);

            return str_contains($content, 'extends GenericPredictorController')
                || $this->looksLikeLegacyGeneratedController($content);
        }));
    }

    protected function looksLikeLegacyGeneratedController(string $content): bool
    {
        return preg_match('/use\s+App\\\\Models\\\\[^;]*(20\d{2}|Rounds20\d{2})[^;]*;/', $content) === 1
            && preg_match('/view\([\'"][^\'"]*20\d{2}[^\'"]*[\'"]/', $content) === 1;
    }

    protected function generatedModelFiles(): array
    {
        $files = glob(app_path('Models/*.php')) ?: [];

        return array_values(array_filter($files, function (string $file): bool {
            $basename = basename($file);
            if (in_array($basename, $this->protectedModelFiles, true)) {
                return false;
            }

            $content = (string) file_get_contents($file);
            return str_contains($content, 'protected $table')
                && preg_match('/20\d{2}|_rounds/', $content) === 1;
        }));
    }

    protected function generatedViewFiles(): array
    {
        $routeNames = $this->oldMenuRouteNames();
        $files = [];

        foreach ($routeNames as $routeName) {
            $view = str_replace('-', '_', $routeName) . '.blade.php';
            $path = resource_path('views/' . $view);
            if ($this->isDeletableView($path)) {
                $files[] = $path;
            }
        }

        foreach (glob(resource_path('views/*.blade.php')) ?: [] as $file) {
            if ($this->isDeletableView($file) && $this->looksLikeGeneratedPredictorView($file)) {
                $files[] = $file;
            }
        }

        return array_values(array_unique($files));
    }

    protected function isDeletableView(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }

        $relative = str_replace('\\', '/', ltrim(str_replace(resource_path('views'), '', $path), DIRECTORY_SEPARATOR));

        return ! in_array($relative, $this->protectedViewFiles, true);
    }

    protected function looksLikeGeneratedPredictorView(string $file): bool
    {
        $content = (string) file_get_contents($file);

        return str_contains($content, 'analysis-table')
            || str_contains($content, 'btn-get-analysis')
            || str_contains($content, 'Only generate tags for quotas and local_areas');
    }

    protected function generatedExcelFiles(): array
    {
        $files = glob(base_path('data_sheets/*.xlsx')) ?: [];

        return array_values(array_filter($files, function (string $file): bool {
            $name = basename($file);

            return preg_match('/20\d{2}/', $name) === 1
                && preg_match('/\b(MBBS|BDS|Dental|Quota|Rank|DATA)\b/i', $name) === 1;
        }));
    }

    protected function generatedMigrationFiles(): array
    {
        $files = glob(database_path('migrations/*.php')) ?: [];

        return array_values(array_filter($files, function (string $file): bool {
            if (in_array(basename($file), $this->protectedMigrationFiles, true)) {
                return false;
            }

            $content = (string) file_get_contents($file);

            return preg_match('/Schema::create\(\'[^\']*20\d{2}[^\']*\'/', $content) === 1
                || preg_match('/Schema::create\(\'[^\']*_rounds\'/', $content) === 1;
        }));
    }

    protected function generatedTables(): array
    {
        $tables = [];

        foreach ($this->generatedModelFiles() as $file) {
            $content = (string) file_get_contents($file);
            if (preg_match('/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
                $tables[] = $matches[1];
            }
        }

        foreach ($this->generatedMigrationFiles() as $file) {
            $content = (string) file_get_contents($file);
            if (preg_match_all('/Schema::create\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
                $tables = array_merge($tables, $matches[1]);
            }
        }

        $tables = array_merge($tables, $this->generatedDatabaseTables());

        $tables = array_unique(array_filter($tables, function (string $table): bool {
            return $this->isGeneratedRankTable($table);
        }));

        $tables = array_values(array_filter($tables, fn ($table) => Schema::hasTable($table)));
        sort($tables);

        return $tables;
    }

    protected function generatedDatabaseTables(): array
    {
        $rows = DB::select('SHOW TABLES');
        $tables = [];

        foreach ($rows as $row) {
            $values = array_values((array) $row);
            if (isset($values[0]) && is_string($values[0])) {
                $tables[] = $values[0];
            }
        }

        return array_values(array_filter($tables, fn (string $table): bool => $this->isGeneratedRankTable($table)));
    }

    protected function isGeneratedRankTable(string $table): bool
    {
        if (in_array($table, $this->protectedTables, true)) {
            return false;
        }

        // Old code-generation imports created standalone rank tables with a year in the table name
        // and separate round tables ending in _rounds. New dynamic tables are explicitly protected above.
        return preg_match('/20\d{2}/', $table) === 1
            || str_ends_with($table, '_rounds');
    }

    protected function deleteFiles(array $files): int
    {
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file) && File::delete($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    protected function dropTables(array $tables): int
    {
        $dropped = 0;

        Schema::disableForeignKeyConstraints();
        try {
            foreach ($tables as $table) {
                if (in_array($table, $this->protectedTables, true) || ! Schema::hasTable($table)) {
                    continue;
                }

                Schema::dropIfExists($table);
                $dropped++;
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return $dropped;
    }

    protected function cleanGeneratedRoutes(array $files): void
    {
        $routesFile = base_path('routes/web.php');
        $content = (string) file_get_contents($routesFile);
        $generatedRoutes = $this->oldMenuRouteNames();
        $generatedControllers = array_map(
            fn (string $file): string => pathinfo($file, PATHINFO_FILENAME),
            array_filter($files, fn (string $file): bool => dirname($file) === app_path('Http/Controllers'))
        );

        $updated = preg_replace_callback(
            '/^Route::get\(\s*[\'"]\/([^\'"]+)[\'"]\s*,\s*\[App\\\\Http\\\\Controllers\\\\([^:\]]+)::class,\s*[\'"]index[\'"]\]\s*\)->name\(\s*[\'"]([^\'"]+)[\'"]\s*\);\R?/m',
            function (array $matches) use ($generatedRoutes, $generatedControllers): string {
                $controller = $matches[2];
                $routeName = $matches[3];

                if ($routeName === 'results.show' || $this->isProtectedRouteName($routeName)) {
                    return $matches[0];
                }

                return in_array($routeName, $generatedRoutes, true)
                    || in_array($controller, $generatedControllers, true)
                        ? ''
                        : $matches[0];
            },
            $content
        );

        if ($updated !== null && $updated !== $content) {
            file_put_contents($routesFile, $updated);
            $this->line('Cleaned generated route entries from routes/web.php.');
        }
    }

    protected function isProtectedRouteName(string $routeName): bool
    {
        return $routeName === 'home'
            || $routeName === 'dashboard'
            || $routeName === 'results.show'
            || str_starts_with($routeName, 'auth.')
            || str_starts_with($routeName, 'login')
            || str_starts_with($routeName, 'logout')
            || str_starts_with($routeName, 'register')
            || str_starts_with($routeName, 'admin.')
            || str_starts_with($routeName, 'import.');
    }

    protected function cleanGeneratedMenus(): void
    {
        $menusPath = config_path('menus.php');
        if (! is_file($menusPath)) {
            return;
        }

        $menus = include $menusPath;
        if (! is_array($menus)) {
            $this->warn('Skipped config/menus.php cleanup because it did not return an array.');
            return;
        }

        $oldRouteNames = $this->oldMenuRouteNames();
        foreach ($menus as $group => $items) {
            $menus[$group] = array_values(array_filter((array) $items, function ($item) use ($oldRouteNames): bool {
                return ! is_array($item)
                    || ! isset($item['route'])
                    || ! in_array($item['route'], $oldRouteNames, true);
            }));

            if ($menus[$group] === []) {
                unset($menus[$group]);
            }
        }

        file_put_contents($menusPath, "<?php\n\nreturn " . var_export($menus, true) . ";\n");
        $this->line('Cleaned generated entries from config/menus.php.');
    }

    protected function oldMenuRouteNames(): array
    {
        $menusPath = config_path('menus.php');
        if (! is_file($menusPath)) {
            return [];
        }

        $menus = include $menusPath;
        $routes = [];

        foreach ((array) $menus as $items) {
            foreach ((array) $items as $item) {
                if (is_array($item) && isset($item['route'])) {
                    $routes[] = $item['route'];
                }
            }
        }

        return array_values(array_unique($routes));
    }

    protected function relativePath(string $path): string
    {
        return str_replace('\\', '/', str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path));
    }
}
