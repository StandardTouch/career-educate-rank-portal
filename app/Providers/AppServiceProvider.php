<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/SmsHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
            $pageTitle = 'All India Quota MBBS'; // default fallback

            if ($routeName) {
                // Try to locate in config/menus.php first
                $menus = config('menus') ?? [];
                $foundLabel = null;

                foreach ($menus as $year => $items) {
                    foreach ($items as $item) {
                        if ($item['route'] === $routeName) {
                            $foundLabel = $item['label'];
                            break 2;
                        }
                    }
                }

                if ($foundLabel) {
                    $pageTitle = $foundLabel;
                } else {
                    // Generate heading dynamically from route name
                    $cleanName = str_replace(
                        ['-analysis-completed', '-analysis', '-data', '-'],
                        ['', '', '', ' '],
                        $routeName
                    );
                    $pageTitle = ucwords(trim($cleanName));

                    $replacements = [
                        'Mbbs' => 'MBBS',
                        'Bds' => 'BDS',
                        'Govt' => 'Government',
                        'Man' => 'Management',
                        'Up' => 'UP',
                    ];
                    foreach ($replacements as $search => $replace) {
                        $pageTitle = str_replace($search, $replace, $pageTitle);
                    }
                }
            } else {
                // Fallback using request path if no route name exists
                $path = request()->path();
                if ($path && $path !== '/') {
                    $cleanPath = str_replace(
                        ['-analysis-completed', '-analysis', '-data', '-'],
                        ['', '', '', ' '],
                        $path
                    );
                    $pageTitle = ucwords(trim($cleanPath));

                    $replacements = [
                        'Mbbs' => 'MBBS',
                        'Bds' => 'BDS',
                        'Govt' => 'Government',
                        'Man' => 'Management',
                        'Up' => 'UP',
                    ];
                    foreach ($replacements as $search => $replace) {
                        $pageTitle = str_replace($search, $replace, $pageTitle);
                    }
                }
            }

            $view->with('pageTitle', $pageTitle);
        });
    }
}
