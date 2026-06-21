@php
    $routeName = Route::currentRouteName();

    $rawYearMenus = config('menus');
    $currentDataset = request()->route('dataset');
    $currentDatasetSlug = is_object($currentDataset) ? ($currentDataset->slug ?? null) : $currentDataset;

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
                ];
            }
        }
    } catch (\Throwable $exception) {
        $rawYearMenus = config('menus');
    }

    $yearMenus = [];

    $activeYear = null;

    foreach ($rawYearMenus as $yearGroup => $items) {
        preg_match('/\d{4}/', $yearGroup, $matches);
        $yearNumber = $matches[0] ?? $yearGroup;
        $displayYear = preg_match('/^\s*neet\s+/i', $yearGroup) ? $yearGroup : 'NEET ' . $yearNumber;

        $yearMenus[$yearGroup] = [
            'label' => $displayYear,
            'ug' => [
                'MBBS' => [],
                'BDS' => [],
            ],
        ];

        foreach ($items as $item) {
            $courseText = strtolower(($item['label'] ?? '') . ' ' . ($item['route'] ?? ''));
            $course = (str_contains($courseText, 'bds') || str_contains($courseText, 'dental')) ? 'BDS' : 'MBBS';
            $yearMenus[$yearGroup]['ug'][$course][] = $item;
        }

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
            <a href="{{ route('home') }}" class="{{ $routeName === 'home' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                Home
            </a>

            @auth
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="{{ $routeName === 'admin.dashboard' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                        Admin
                    </a>
                    <a href="{{ route('admin.users') }}" class="{{ $routeName === 'admin.users' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                        Users
                    </a>
                    <a href="{{ route('admin.payments') }}" class="{{ $routeName === 'admin.payments' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                        Payments
                    </a>
                    <a href="{{ route('import.excel') }}" class="{{ $routeName === 'import.excel' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                        Import
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="{{ $routeName === 'dashboard' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                        Dashboard
                    </a>
                    <a href="{{ route('neet.analysis') }}" class="{{ $routeName === 'neet.analysis' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                        NEET Analysis 2025
                    </a>
                    <a href="#" class="hover:text-rose-500 transition-colors px-3 py-2 rounded-lg">
                        MBBS Study Abroad
                    </a>
                    <a href="{{ route('profile') }}" class="{{ $routeName === 'profile' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                        Profile
                    </a>
                @endif
            @endauth

            <!-- Notifications Dropdown (Visible to everyone) -->
            <div class="relative group results-year-menu">
                <button
                    type="button"
                    class="results-year-trigger px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600 transition-colors inline-flex items-center gap-2"
                    aria-expanded="false"
                >
                    <span>Notifications</span>
                    <span class="results-year-caret text-[10px] leading-none transition-transform">v</span>
                </button>
                <div
                    class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                    style="width: min(24rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                >
                    <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Latest Updates</div>
                    <div class="grid gap-1 px-2 pb-2">
                        <a href="{{ asset('Shaheen-Kyrgyzstan-Booklet.pdf') }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                            Shaheen Kyrgyzstan Booklet
                        </a>
                        <a href="{{ asset('Shaheen-MSIT-Tajikistan-Booklet.pdf') }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                            Shaheen MSIT Tajikistan Booklet
                        </a>
                        <a href="{{ asset('MCC-counselling-flow.pdf') }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                            MCC Counselling Flow
                        </a>
                        <a href="{{ asset('notifications.pdf') }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
                            Other Notifications
                        </a>
                    </div>
                </div>
            </div>

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
                                                    {{ $item['label'] ?? '#' }}
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

            <a href="tel:9686601088" class="text-rose-500 font-semibold transition-colors px-3 py-2 rounded-lg animate-pulse whitespace-nowrap border border-rose-200 bg-rose-50/50 hover:bg-rose-100">
                MBBS STUDY ABROAD CONTACT NUMBER 9686601088
            </a>
        </nav>

        <div class="flex items-center gap-3">
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
