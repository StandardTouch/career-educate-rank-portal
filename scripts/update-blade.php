<?php

declare(strict_types=1);

$options = getopt('', ['path::', 'dry-run', 'help']);

if (isset($options['help'])) {
    echo "Usage: php scripts/update-analysis-ui.php [--path=resources/views] [--dry-run]\n";
    exit(0);
}

$basePath = getcwd();
$targetPath = $options['path'] ?? 'resources/views';
$dryRun = array_key_exists('dry-run', $options);

if (!is_string($targetPath) || $targetPath === '') {
    fwrite(STDERR, "Invalid --path value.\n");
    exit(1);
}

$targetPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetPath);
$targetDir = preg_match('/^[A-Za-z]:\\\\|^\\\\\\\\|^\//', $targetPath)
    ? $targetPath
    : $basePath . DIRECTORY_SEPARATOR . $targetPath;

if (!is_dir($targetDir)) {
    fwrite(STDERR, "Blade directory not found: {$targetDir}\n");
    exit(1);
}

$newCss = <<<'CSS'
        #column-visibility-list {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .colvis-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-width: 220px;
            padding: 10px 16px;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .colvis-toggle:hover,
        .colvis-dropdown.open .colvis-toggle {
            border-color: #f43f5e;
            color: #f43f5e;
        }

        .colvis-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            z-index: 30;
            display: none;
            width: min(320px, calc(100vw - 32px));
            max-height: 320px;
            overflow-y: auto;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #ffffff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
        }

        .colvis-dropdown.open .colvis-menu {
            display: block;
        }

        .colvis-option {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px;
            border-radius: 0.5rem;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        .colvis-option:hover {
            background: #fff1f2;
            color: #e11d48;
        }

        .colvis-option input {
            width: 16px;
            height: 16px;
            accent-color: #f43f5e;
        }
CSS;

$newContainer = <<<'JS'
        // Container for custom column visibility dropdown
        const colVisContainer = document.createElement('div');
        colVisContainer.id = 'column-visibility-list';
        colVisContainer.className = 'column-visibility-dropdown-wrapper';
// Insert container before the table element
const tableEl = document.getElementById('analysis-table');
if (tableEl && tableEl.parentNode) {
    tableEl.parentNode.insertBefore(colVisContainer, tableEl);
}
JS;

$newReload = <<<'JS'
                // Reload Yajra DataTable with current parameters
                analysisTable.ajax.reload(function() {
                    const resultsPanel = document.getElementById('results-panel');
                    if (resultsPanel) {
                        resultsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
JS;

$newFunction = <<<'JS'
        // Define function to create column visibility dropdown
        function initColumnVisibility() {
    if (!analysisTable) return;

    const container = document.getElementById('column-visibility-list');
    if (!container) return;

    container.innerHTML = '';

    const dropdown = document.createElement('div');
    dropdown.className = 'colvis-dropdown';

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'colvis-toggle';

    const toggleText = document.createElement('span');
    const toggleIcon = document.createElement('span');
    toggleIcon.setAttribute('aria-hidden', 'true');
    toggleIcon.textContent = 'v';

    toggle.appendChild(toggleText);
    toggle.appendChild(toggleIcon);

    const menu = document.createElement('div');
    menu.className = 'colvis-menu custom-scrollbar';

    function updateToggleText() {
        const total = analysisTable.columns().count();
        const visible = analysisTable.columns(':visible').count();
        toggleText.textContent = `Columns (${visible}/${total})`;
    }

    toggle.addEventListener('click', function(event) {
        event.stopPropagation();
        dropdown.classList.toggle('open');
    });

    menu.addEventListener('click', function(event) {
        event.stopPropagation();
    });

    analysisTable.columns().every(function(idx) {
        const col = this;
        const title = $(col.header()).text().trim() || `Column ${idx + 1}`;

        const label = document.createElement('label');
        label.className = 'colvis-option';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = col.visible();

        checkbox.addEventListener('change', function() {
            col.visible(this.checked);
            updateToggleText();
        });

        const text = document.createElement('span');
        text.textContent = title;

        label.appendChild(checkbox);
        label.appendChild(text);
        menu.appendChild(label);
    });

    dropdown.appendChild(toggle);
    dropdown.appendChild(menu);
    container.appendChild(dropdown);
    updateToggleText();

    document.addEventListener('click', function() {
        dropdown.classList.remove('open');
    });
}
JS;

$patterns = [
    'css' => [
        '/        #column-visibility-list \{.*?        \.colvis-button:hover \{\R            border-color: #f43f5e;\R        \}\R/s',
        $newCss . "\n",
    ],
    'container' => [
        '/        \/\/ Container for custom column visibility checkboxes \(vertical list\)\R        const colVisContainer = document\.createElement\(\'div\'\);\R        colVisContainer\.id = \'column-visibility-list\';\R        colVisContainer\.style\.marginBottom = \'10px\';\R        colVisContainer\.style\.display = \'flex\';\R        colVisContainer\.style\.flexWrap = \'wrap\';\R        colVisContainer\.style\.gap = \'10px\';\R        colVisContainer\.style\.marginBottom = \'20px\';\R\/\/ Insert container before the table element\Rconst tableEl = document\.getElementById\(\'analysis-table\'\);\Rif \(tableEl && tableEl\.parentNode\) \{\R    tableEl\.parentNode\.insertBefore\(colVisContainer, tableEl\);\R\}\R/s',
        $newContainer . "\n",
    ],
    'reload' => [
        '/                \/\/ Reload Yajra DataTable with current parameters\R                analysisTable\.ajax\.reload\(\);/s',
        $newReload,
    ],
    'function' => [
        '/        \/\/ Define function to create vertical column visibility checkboxes\R        function initColumnVisibility\(\) \{.*?\R\}\R\R        \/\/ Update the textual description of applied filters above result table/s',
        $newFunction . "\n\n        // Update the textual description of applied filters above result table",
    ],
];

$filesScanned = 0;
$filesChanged = 0;
$skipped = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetDir, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || !str_ends_with($fileInfo->getFilename(), '.blade.php')) {
        continue;
    }

    $path = $fileInfo->getPathname();
    $contents = file_get_contents($path);
    if ($contents === false) {
        $skipped[] = $path . ' (read failed)';
        continue;
    }

    if (!str_contains($contents, 'function initColumnVisibility') || !str_contains($contents, '#btn-get-analysis')) {
        continue;
    }

    $filesScanned++;
    $updated = $contents;
    $missingMatches = [];

    foreach ($patterns as $name => [$pattern, $replacement]) {
        $count = 0;
        $updated = preg_replace($pattern, $replacement, $updated, 1, $count);
        if ($updated === null) {
            fwrite(STDERR, "Regex failed while processing {$path} ({$name}).\n");
            exit(1);
        }

        if ($count === 0 && !str_contains($contents, 'colvis-dropdown')) {
            $missingMatches[] = $name;
        }
    }

    if ($missingMatches !== []) {
        $skipped[] = $path . ' (missing: ' . implode(', ', $missingMatches) . ')';
    }

    if ($updated === $contents) {
        continue;
    }

    $filesChanged++;
    echo ($dryRun ? '[dry-run] Would update: ' : 'Updated: ') . $path . PHP_EOL;

    if (!$dryRun && file_put_contents($path, $updated) === false) {
        fwrite(STDERR, "Write failed: {$path}\n");
        exit(1);
    }
}

echo PHP_EOL;
echo "Blade analysis files scanned: {$filesScanned}\n";
echo ($dryRun ? 'Blade files that would change: ' : 'Blade files changed: ') . $filesChanged . "\n";

if ($skipped !== []) {
    echo PHP_EOL . "Files needing manual review:\n";
    foreach ($skipped as $path) {
        echo "- {$path}\n";
    }
}
