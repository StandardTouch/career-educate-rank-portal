<?php
namespace App\Console;

class ScaffoldHelper
{
    /**
     * Parse a filename to extract state slug, year and descriptor.
     *
     * Example: "Andhra Pradesh BDS Govt Quota - 2024 Analysis.xlsx"
     *   -> state: "andhra_pradesh"
     *   -> year: 2024
     *   -> descriptor: "govt_quota"
     */
    public static function parseFileName(string $filename): array
    {
        // Remove extension
        $name = pathinfo($filename, PATHINFO_FILENAME);

        // Extract year (last four digits)
        if (preg_match('/(20\d{2})$/', $name, $yearMatch)) {
            $year = (int)$yearMatch[1];
        } else {
            throw new \InvalidArgumentException("Year not found in filename: $filename");
        }

        // Define keywords to split on
        $keywords = ['BDS', 'MBBS', 'Quota'];
        $pattern = '/(' . implode('|', $keywords) . ')/i';
        $parts = preg_split($pattern, $name, 2, PREG_SPLIT_DELIM_CAPTURE);
        if (count($parts) < 3) {
            // fallback: assume whole name before year is state
            $statePart = trim(str_replace((string)$year, '', $name));
            $descriptor = '';
        } else {
            $statePart = trim($parts[0]);
            $descriptor = trim($parts[1] . ' ' . $parts[2]);
        }

        // Normalise state slug
        $stateSlug = strtolower(str_replace([' ', '-', '.'], '_', $statePart));
        $stateSlug = preg_replace('/_+/', '_', $stateSlug);
        $stateSlug = trim($stateSlug, '_');

        // Normalise descriptor (remove year and extra words, keep quota/management info)
        $descriptor = preg_replace('/\s*' . $year . '\s*/', '', $descriptor);
        $descriptor = strtolower(str_replace([' ', '-', '.'], '_', $descriptor));
        $descriptor = preg_replace('/_+/', '_', $descriptor);
        $descriptor = trim($descriptor, '_');

        return [
            'state' => $stateSlug,
            'year' => $year,
            'descriptor' => $descriptor,
        ];
    }

    /** Check if a migration file for a given table already exists */
    public static function migrationExists(string $tableName): bool
    {
        $migrationsPath = base_path('database/migrations');
        $files = glob($migrationsPath . '/*create_' . $tableName . '_table.php');
        return $files && count($files) > 0;
    }

    public static function modelExists(string $className): bool
    {
        return class_exists($className);
    }

    public static function controllerExists(string $className): bool
    {
        return class_exists($className);
    }

    public static function viewExists(string $viewPath): bool
    {
        return file_exists(resource_path('views/' . $viewPath . '.blade.php'));
    }

    public static function routeExists(string $uri): bool
    {
        $routesFile = base_path('routes/web.php');
        $content = file_get_contents($routesFile);
        return strpos($content, "'$uri'") !== false || strpos($content, "\"$uri\"") !== false;
    }
}
?>
