<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ScaffoldHelper
{
    /**
     * Check if a controller class file exists.
     */
    public static function controllerExists(string $controllerClass): bool
    {
        // Convert fully qualified class name to file path
        $relative = str_replace('App\\', '', $controllerClass);
        $path = base_path('app/Http/Controllers/' . str_replace('\\', '/', $relative) . '.php');
        return File::exists($path);
    }

    /**
     * Check if a Blade view file exists.
     */
    public static function viewExists(string $viewName): bool
    {
        $path = resource_path('views/' . $viewName . '.blade.php');
        return File::exists($path);
    }

    /**
     * Check if a route URI is already defined in routes/web.php.
     */
    public static function routeExists(string $uri): bool
    {
        $routesFile = base_path('routes/web.php');
        if (!File::exists($routesFile)) {
            return false;
        }
        $content = File::get($routesFile);
        return strpos($content, "Route::get('/{$uri}'") !== false;
    }

    /**
     * Parse Excel filename into components.
     * Expected format: "<STATE> <YEAR> <DESCRIPTOR>.xlsx" or variations.
     */
    public static function parseFileName(string $filename): array
    {
        // Remove extension
        $name = pathinfo($filename, PATHINFO_FILENAME);
        // Split by spaces
        $parts = preg_split('/\s+/', $name);
        // Find year (4 digits)
        $year = null;
        foreach ($parts as $i => $part) {
            if (preg_match('/\d{4}/', $part)) {
                $year = $part;
                $stateParts = array_slice($parts, 0, $i);
                $descriptorParts = array_slice($parts, $i + 1);
                break;
            }
        }
        if ($year === null) {
            throw new \Exception("Year not found in filename {$filename}");
        }
        $state = strtolower(implode('_', $stateParts));
        $descriptor = strtolower(implode('_', $descriptorParts));
        return [
            'state' => $state,
            'year' => $year,
            'descriptor' => $descriptor,
        ];
    }

    /**
     * Check if a migration for given table exists.
     */
    public static function migrationExists(string $tableName): bool
    {
        $files = glob(database_path('migrations/*.php'));
        foreach ($files as $file) {
            if (strpos($file, $tableName) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a model class exists.
     */
    public static function modelExists(string $fullClass): bool
    {
        $classPath = str_replace('App\\', '', $fullClass);
        $path = base_path('app/Models/' . str_replace('\\', '/', $classPath) . '.php');
        return File::exists($path);
    }

    /**
     * Create a basic controller if it does not exist.
     */
    public static function createController(string $controllerClass, string $state, string $year, string $descriptor): void
    {

        $parts = explode('\\', $controllerClass);
        $className = array_pop($parts);
        $namespace = implode('\\', $parts);
        $path = base_path('app/Http/Controllers/' . str_replace('\\', '/', $controllerClass) . '.php');
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $viewName = $state . '_' . $year . ($descriptor ? '_' . $descriptor : '');
        $content = "<?php\n\nnamespace {$namespace};\n\nuse App\Http\Controllers\Controller;\n\nclass {$className} extends Controller\n{\n    public function index()\n    {\n        return view('{$viewName}');\n    }\n}\n";
        file_put_contents($path, $content);
                Log::info("Created controller {$controllerClass}");
    }
}
?>
