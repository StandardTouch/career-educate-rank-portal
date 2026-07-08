@php
    $routeName = Route::currentRouteName();

    $rawYearMenus = config('menus');
    $currentDataset = request()->route('dataset');
    $currentAnalysisDataset = request()->route('analysis_dataset');
    $currentDatasetSlug = is_object($currentDataset) ? ($currentDataset->slug ?? null) : $currentDataset;
    $currentDatasetSlug = $currentDatasetSlug ?: (is_object($currentAnalysisDataset) ? ($currentAnalysisDataset->slug ?? null) : $currentAnalysisDataset);
    $notificationDocuments = collect();
    $menuRootFolders = collect();
    $documentDropdowns = collect([
        'Notifications' => collect(),
        'MBBS Study Abroad' => collect(),
    ]);

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('datasets')) {
            $datasets = \App\Models\Dataset::query()
                ->where('is_active', true)
                ->orderByDesc('year')
                ->orderByRaw('sort_order is null')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();

            foreach ($datasets as $dataset) {
                $group = 'Results ' . ($dataset->year ?? 'Dynamic');
                $rawYearMenus[$group] = $rawYearMenus[$group] ?? [];
                $rawYearMenus[$group][] = [
                    'label' => $dataset->title,
                    'route' => 'results.show',
                    'params' => ['dataset' => $dataset->slug],
                    'dataset_slug' => $dataset->slug,
                    'course' => $dataset->course,
                ];
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('analysis_datasets')) {
            $analysisDatasets = \App\Models\AnalysisDataset::query()
                ->where('is_active', true)
                ->orderByDesc('year')
                ->orderByRaw('sort_order is null')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();

            foreach ($analysisDatasets as $dataset) {
                $group = 'Analysis ' . ($dataset->year ?? 'Dynamic');
                $rawYearMenus[$group] = $rawYearMenus[$group] ?? [];
                $rawYearMenus[$group][] = [
                    'label' => $dataset->title,
                    'route' => 'analysis.show',
                    'params' => ['analysis_dataset' => $dataset->slug],
                    'dataset_slug' => $dataset->slug,
                    'course' => $dataset->course,
                ];
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('menu_folders')) {
            $menuRootFolders = \App\Models\MenuFolder::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with([
                    'activeNotificationDocuments',
                    'activeChildren.activeNotificationDocuments',
                    'activeChildren.activeChildren.activeNotificationDocuments',
                    'activeChildren.activeChildren.activeChildren.activeNotificationDocuments',
                ])
                ->orderByRaw('sort_order is null')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('notification_documents')) {
            $notificationDocuments = \App\Models\NotificationDocument::query()
                ->where('is_active', true)
                ->orderByRaw('sort_order is null')
                ->orderBy('sort_order')
                ->latest('id')
                ->get();

            $documentDropdowns = $notificationDocuments
                ->groupBy(fn ($document) => trim((string) ($document->dropdown_name ?: 'Notifications')))
                ->union($documentDropdowns);
        }
    } catch (\Throwable $exception) {
        $rawYearMenus = config('menus');
        $notificationDocuments = collect();
        $menuRootFolders = collect();
        $documentDropdowns = collect([
            'Notifications' => collect(),
            'MBBS Study Abroad' => collect(),
        ]);
    }

    $yearMenus = [];
    $notificationsFolder = $menuRootFolders->first(fn ($folder) => ($folder->slug ?? '') === 'notifications' || strcasecmp((string) $folder->title, 'Notifications') === 0);
    $mbbsStudyAbroadFolder = $menuRootFolders->first(fn ($folder) => ($folder->slug ?? '') === 'mbbs-study-abroad' || strcasecmp((string) $folder->title, 'MBBS Study Abroad') === 0);

    $activeYear = null;

    foreach ($rawYearMenus as $yearGroup => $items) {
        preg_match('/\d{4}/', $yearGroup, $matches);
        $yearNumber = $matches[0] ?? $yearGroup;
        
        $displayYear = preg_match('/^\s*neet\s+/i', $yearGroup) ? $yearGroup : 'NEET ' . $yearNumber;

        if (str_contains($yearGroup, 'Analysis')) {
            $displayYear = 'NEET ' . $yearNumber . ' Predicted Rank';
        }

        $yearMenus[$yearGroup] = [
            'label' => $displayYear,
            'ug' => [],
        ];

        foreach ($items as $item) {
            $course = strtoupper(trim((string) ($item['course'] ?? '')));

            if ($course === '') {
                $courseText = strtolower(($item['label'] ?? '') . ' ' . ($item['route'] ?? ''));
                $course = (str_contains($courseText, 'bds') || str_contains($courseText, 'dental')) ? 'BDS' : 'MBBS';
            }

            $yearMenus[$yearGroup]['ug'][$course] = $yearMenus[$yearGroup]['ug'][$course] ?? [];
            $yearMenus[$yearGroup]['ug'][$course][] = $item;
        }

        $orderedCourses = [];
        foreach (['MBBS', 'BDS'] as $knownCourse) {
            if (array_key_exists($knownCourse, $yearMenus[$yearGroup]['ug'])) {
                $orderedCourses[$knownCourse] = $yearMenus[$yearGroup]['ug'][$knownCourse];
            }
        }

        foreach ($yearMenus[$yearGroup]['ug'] as $course => $courseItems) {
            if (! array_key_exists($course, $orderedCourses)) {
                $orderedCourses[$course] = $courseItems;
            }
        }

        $yearMenus[$yearGroup]['ug'] = $orderedCourses;

        if ($routeName && str_contains($routeName, $yearNumber)) {
            $activeYear = $yearGroup;
        }
    }
@endphp

<header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-16 py-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('career.png') }}" alt="Logo" class="header-logo">
                <!-- <span class="font-bold text-lg text-slate-900 tracking-tight">Career Educate</span> -->
            </a>

        </div>

        <nav class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-600">
            <a href="{{ route('home') }}" class="{{ $routeName === 'home' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg whitespace-nowrap">
                Home
            </a>

            @auth
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="{{ $routeName === 'admin.dashboard' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg whitespace-nowrap">
                        Admin
                    </a>
                    <a href="{{ route('admin.users') }}" class="{{ $routeName === 'admin.users' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg whitespace-nowrap">
                        Users
                    </a>
                    <a href="{{ route('admin.payments') }}" class="{{ $routeName === 'admin.payments' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg whitespace-nowrap">
                        Payments
                    </a>
                    <div class="relative group results-year-menu">
                        <button
                            type="button"
                            class="results-year-trigger px-3 py-2 rounded-lg border {{ in_array($routeName, ['admin.call-details', 'admin.call-history', 'admin.career-educate-call-log'], true) ? 'border-rose-300 text-rose-600 bg-rose-50' : 'border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600' }} transition-colors inline-flex items-center gap-2 whitespace-nowrap"
                            aria-expanded="false"
                        >
                            <span>Call Details</span>
                            <span class="results-year-caret text-[10px] leading-none transition-transform">v</span>
                        </button>
                        <div
                            class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                            style="width: min(20rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                        >
                            <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Call Details</div>
                            <div class="grid gap-1 px-2 pb-2">
                                <a href="{{ route('admin.call-details') }}" class="results-menu-link {{ in_array($routeName, ['admin.call-details', 'admin.call-history'], true) ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-700' }} rounded-xl px-3 py-2 text-sm transition-colors block">
                                    Student Call Lookup
                                </a>
                                <a href="{{ route('admin.career-educate-call-log') }}" class="results-menu-link {{ $routeName === 'admin.career-educate-call-log' ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-700' }} rounded-xl px-3 py-2 text-sm transition-colors block">
                                    Career Educate Call Log
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="relative group results-year-menu">
                        <button
                            type="button"
                            class="results-year-trigger px-3 py-2 rounded-lg border {{ in_array($routeName, ['import.excel', 'import.excel.confirm', 'admin.imports', 'import.analysis', 'import.analysis.confirm', 'notifications.import', 'notifications.import.confirm'], true) ? 'border-rose-300 text-rose-600 bg-rose-50' : 'border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600' }} transition-colors inline-flex items-center gap-2 whitespace-nowrap"
                            aria-expanded="false"
                        >
                            <span>View Imports</span>
                            <span class="results-year-caret text-[10px] leading-none transition-transform">v</span>
                        </button>
                        <div
                            class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                            style="width: min(18rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                        >
                            <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Import Tools</div>
                            <div class="grid gap-1 px-2 pb-2">
                                <a href="{{ route('admin.imports') }}" class="results-menu-link {{ $routeName === 'admin.imports' ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-700' }} rounded-xl px-3 py-2 text-sm transition-colors block">
                                    Manage All Imports
                                </a>
                                <a href="{{ route('import.excel') }}" class="results-menu-link {{ $routeName === 'import.excel' ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-700' }} rounded-xl px-3 py-2 text-sm transition-colors block">
                                    Import Results
                                </a>
                                <a href="{{ route('import.analysis') }}" class="results-menu-link {{ $routeName === 'import.analysis' ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-700' }} rounded-xl px-3 py-2 text-sm transition-colors block">
                                    Import Predicted Rank
                                </a>
                                <a href="{{ route('notifications.import') }}" class="results-menu-link {{ $routeName === 'notifications.import' ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-700' }} rounded-xl px-3 py-2 text-sm transition-colors block">
                                    Import PDF
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}" class="{{ $routeName === 'dashboard' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg whitespace-nowrap">
                        Dashboard
                    </a>
                    <a href="{{ route('neet.analysis') }}" class="{{ $routeName === 'neet.analysis' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg whitespace-nowrap">
                        NEET Analysis 2025
                    </a>
                    <a href="{{ route('profile') }}" class="{{ $routeName === 'profile' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg whitespace-nowrap">
                        Profile
                    </a>
                @endif
            @endauth

            <!-- MBBS Study Abroad Dropdown -->
            <div class="relative group results-year-menu">
                <button
                    type="button"
                    class="results-year-trigger px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600 transition-colors inline-flex items-center gap-2 whitespace-nowrap"
                    aria-expanded="false"
                >
                    <span>MBBS Study Abroad</span>
                    <span class="results-year-caret text-[10px] leading-none transition-transform">v</span>
                </button>
                <div
                    class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                    style="width: min(20rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                >
                    <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Booklets</div>
                    <div class="grid gap-1 px-2 pb-2">
                        <a href="{{ asset('Shaheen-Kyrgyzstan-Booklet.pdf') }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                            Kyrgyzstan Booklet
                        </a>
                        <a href="{{ asset('Shaheen-MSIT-Tajikistan-Booklet.pdf') }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                            MSIT Tajikistan Booklet
                        </a>
                        @if ($mbbsStudyAbroadFolder)
                            @include('partials.menu-folder-items', ['folder' => $mbbsStudyAbroadFolder])
                        @else
                            @foreach ($documentDropdowns->get('MBBS Study Abroad', collect()) as $document)
                                <a href="{{ route('notifications.view', $document) }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                                    {{ \Illuminate\Support\Str::upper($document->title) }}
                                </a>
                            @endforeach
                        @endif
                    </div>
                    <div class="flex items-center gap-2 border-l border-slate-200 pl-3 ml-2">
                        <a href="tel:9686601088" class="text-rose-500 font-semibold transition-colors px-3 py-2 rounded-lg animate-pulse whitespace-nowrap border border-rose-200 bg-rose-50/50 hover:bg-rose-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.20l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            Contact: 9686601088
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notifications Dropdown -->
            <div class="relative group results-year-menu">
                <button
                    type="button"
                    class="results-year-trigger px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600 transition-colors inline-flex items-center gap-2 whitespace-nowrap"
                    aria-expanded="false"
                >
                    <span>Notifications</span>
                    <span class="results-year-caret text-[10px] leading-none transition-transform">v</span>
                </button>
                <div
                    class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                    style="width: min(20rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                >
                    <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Updates</div>
                    <div class="grid gap-1 px-2 pb-2">
                        @if ($notificationsFolder)
                            @include('partials.menu-folder-items', ['folder' => $notificationsFolder])
                            @if (($notificationsFolder->activeNotificationDocuments ?? collect())->isEmpty() && ($notificationsFolder->activeChildren ?? collect())->isEmpty())
                                <div class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-400">
                                    No notifications uploaded yet.
                                </div>
                            @endif
                        @else
                            @forelse ($documentDropdowns->get('Notifications', collect()) as $document)
                                <a href="{{ route('notifications.view', $document) }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                                    {{ \Illuminate\Support\Str::upper($document->title) }}
                                </a>
                            @empty
                                <div class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-400">
                                    No notifications uploaded yet.
                                </div>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>

            @foreach ($menuRootFolders as $rootFolder)
                @continue(in_array($rootFolder->slug ?? '', ['notifications', 'mbbs-study-abroad'], true))
                <div class="relative group results-year-menu">
                    <button
                        type="button"
                        class="results-year-trigger px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600 transition-colors inline-flex items-center gap-2 whitespace-nowrap"
                        aria-expanded="false"
                    >
                        <span>{{ $rootFolder->title }}</span>
                        <span class="results-year-caret text-[10px] leading-none transition-transform">v</span>
                    </button>
                    <div
                        class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                        style="width: min(22rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                    >
                        <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $rootFolder->title }}</div>
                        <div class="grid gap-1 px-2 pb-2">
                            @include('partials.menu-folder-items', ['folder' => $rootFolder])
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($menuRootFolders->isEmpty())
            @foreach ($documentDropdowns as $dropdownName => $documents)
                @continue(in_array($dropdownName, ['Notifications', 'MBBS Study Abroad'], true) || $documents->isEmpty())
                <div class="relative group results-year-menu">
                    <button
                        type="button"
                        class="results-year-trigger px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600 transition-colors inline-flex items-center gap-2 whitespace-nowrap"
                        aria-expanded="false"
                    >
                        <span>{{ $dropdownName }}</span>
                        <span class="results-year-caret text-[10px] leading-none transition-transform">v</span>
                    </button>
                    <div
                        class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                        style="width: min(20rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                    >
                        <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $dropdownName }}</div>
                        <div class="grid gap-1 px-2 pb-2">
                            @foreach ($documents as $document)
                                <a href="{{ route('notifications.view', $document) }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                                    {{ \Illuminate\Support\Str::upper($document->title) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
            @endif

            @foreach ($yearMenus as $year => $menu)
                <div class="relative group results-year-menu">
                    <button
                        type="button"
                        class="results-year-trigger px-3 py-2 rounded-lg border {{ $activeYear === $year ? 'border-rose-300 text-rose-600 bg-rose-50' : 'border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600' }} transition-colors inline-flex items-center gap-2"
                        aria-expanded="false"
                    >
                        <span>{{ $menu['label'] }}</span>
                        <span class="results-year-caret text-[10px] leading-none transition-transform">v</span>
                    </button>
                    <div
                        class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                        style="width: min(42rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                    >
                        <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $menu['label'] }}</div>
                        <div class="px-2 pb-2">
                            <input type="search" class="results-menu-search w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20" placeholder="Search results...">
                        </div>
                        <details class="results-course-group rounded-xl border border-slate-100 bg-slate-50/60" open>
                            <summary class="cursor-pointer select-none px-3 py-2 text-sm font-bold text-slate-800">UG</summary>
                            <div class="grid gap-2 p-2">
                                @foreach ($menu['ug'] as $course => $items)
                                    @continue(count($items) === 0)
                                    <details class="rounded-xl bg-white border border-slate-100" {{ collect($items)->contains(fn ($item) => $routeName === ($item['route'] ?? null) && (($item['dataset_slug'] ?? null) === null || ($item['dataset_slug'] ?? null) === $currentDatasetSlug)) ? 'open' : '' }}>
                                        <summary class="cursor-pointer select-none px-3 py-2 text-sm font-bold text-rose-600">{{ $course }}</summary>
                                        <div class="grid gap-1 px-2 pb-2">
                                            @foreach ($items as $item)
                                                @php
                                                    $isActiveItem = $routeName === ($item['route'] ?? null)
                                                        && (($item['dataset_slug'] ?? null) === null || ($item['dataset_slug'] ?? null) === $currentDatasetSlug);
                                                @endphp
                                                @if(is_array($item) && !empty($item['route']) && Route::has($item['route']))
                                                    <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                                                @else
                                                    <a href="#"
                                                @endif
                                                    class="results-menu-link {{ $isActiveItem ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-slate-50' }} rounded-xl px-3 py-2 text-sm transition-colors">
                                                    {{ \Illuminate\Support\Str::upper($item['label'] ?? '#') }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </details>
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            <a href="tel:08047285479" class="text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 hover:border-rose-300 font-semibold text-sm px-3 py-1.5 rounded-full transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.20l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                080-472-85479
            </a>
            @if ($routeName === 'home')
                <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="bg-rose-500 hover:bg-rose-600 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all shadow-md shadow-rose-500/10 active:scale-95">
                    Start Predicting
                </a>
            @elseif (auth()->check())
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-full hover:border-rose-300 hover:text-rose-600 transition-colors">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-full hover:border-rose-300 hover:text-rose-600 transition-colors">
                    Login
                </a>
            @endif
        </div>
    </div>
</header>

<style>
    .results-year-panel {
        display: block;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .results-year-panel.hidden {
        display: none;
    }

    .results-year-panel::-webkit-scrollbar {
        width: 10px;
    }

    .results-year-panel::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 9999px;
    }

    .results-year-panel::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
        border: 2px solid #f8fafc;
    }

    .results-year-panel::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<script>
    (function () {
        const menus = Array.from(document.querySelectorAll('.results-year-menu'));

        function closeAll(exceptMenu = null) {
            let hasOpenMenu = false;

            menus.forEach((menu) => {
                if (menu === exceptMenu) {
                    const panel = menu.querySelector('.results-year-panel');
                    if (panel && !panel.classList.contains('hidden')) {
                        hasOpenMenu = true;
                    }
                    return;
                }

                const panel = menu.querySelector('.results-year-panel');
                const trigger = menu.querySelector('.results-year-trigger');
                const caret = menu.querySelector('.results-year-caret');

                panel?.classList.add('hidden');
                trigger?.setAttribute('aria-expanded', 'false');
                if (caret) caret.style.transform = 'rotate(0deg)';
            });

            if (!hasOpenMenu && exceptMenu === null) {
                document.body.style.overflow = '';
            }
        }

        menus.forEach((menu) => {
            const trigger = menu.querySelector('.results-year-trigger');
            const panel = menu.querySelector('.results-year-panel');
            const caret = menu.querySelector('.results-year-caret');
            const search = menu.querySelector('.results-menu-search');

            function positionPanel() {
                const rect = trigger.getBoundingClientRect();
                const viewportPadding = 16;
                const preferredWidth = Math.min(672, window.innerWidth - (viewportPadding * 2));
                const availableHeight = Math.max(220, window.innerHeight - rect.bottom - viewportPadding);
                const left = Math.min(rect.left, window.innerWidth - preferredWidth - viewportPadding);

                panel.style.top = `${rect.bottom + 10}px`;
                panel.style.left = `${Math.max(viewportPadding, left)}px`;
                panel.style.width = `${preferredWidth}px`;
                panel.style.height = `${availableHeight - 10}px`;
                panel.style.maxHeight = `${availableHeight - 10}px`;
                panel.style.overflowY = 'auto';
            }

            trigger?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const isOpen = !panel.classList.contains('hidden');

                if (isOpen) {
                    panel.classList.add('hidden');
                    trigger.setAttribute('aria-expanded', 'false');
                    if (caret) caret.style.transform = 'rotate(0deg)';
                    document.body.style.overflow = '';
                    return;
                }

                closeAll(menu);
                positionPanel();
                panel.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
                search?.focus();
                document.body.style.overflow = 'hidden';
                if (caret) caret.style.transform = 'rotate(180deg)';
            });

            search?.addEventListener('input', () => {
                const term = search.value.trim().toLowerCase();

                menu.querySelectorAll('.results-menu-link').forEach((link) => {
                    link.hidden = term !== '' && !link.textContent.toLowerCase().includes(term);
                });

                menu.querySelectorAll('.results-course-group details').forEach((group) => {
                    group.hidden = Array.from(group.querySelectorAll('.results-menu-link')).every((link) => link.hidden);
                });
            });

            window.addEventListener('resize', () => {
                if (!panel.classList.contains('hidden')) {
                    positionPanel();
                }
            });

            window.addEventListener('scroll', () => {
                if (!panel.classList.contains('hidden')) {
                    positionPanel();
                }
            }, true);
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.results-year-menu')) closeAll();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAll();
            }
        });
    })();
</script>
