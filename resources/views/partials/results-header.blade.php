@php
    $routeName = Route::currentRouteName();

    $yearMenus = config('menus');

    $activeYear = null;

    foreach (array_keys($yearMenus) as $year) {
        if ($routeName && str_contains($routeName, $year)) {
            $activeYear = $year;
            break;
        }
    }
@endphp

<header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-16 py-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-10 h-10 object-contain">
                <span class="font-bold text-lg text-slate-900 tracking-tight">Career Educate</span>
            </a>

        </div>

        <nav class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-600">
            <a href="{{ route('home') }}" class="{{ $routeName === 'home' ? 'text-rose-500 font-semibold' : 'hover:text-rose-500' }} transition-colors px-3 py-2 rounded-lg">
                Home
            </a>

            @foreach ($yearMenus as $year => $items)
                <div class="relative group results-year-menu">
                    <button
                        type="button"
                        class="results-year-trigger px-3 py-2 rounded-lg border {{ $activeYear === $year ? 'border-rose-300 text-rose-600 bg-rose-50' : 'border-slate-200 text-slate-700 hover:border-rose-300 hover:text-rose-600' }} transition-colors inline-flex items-center gap-2"
                        aria-expanded="false"
                    >
                        <span>{{ $year }}</span>
                        <span class="results-year-caret text-[10px] leading-none transition-transform">▼</span>
                    </button>
                    <div
                        class="results-year-panel hidden fixed z-50 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl p-2"
                        style="width: min(42rem, calc(100vw - 2rem)); max-height: min(32rem, calc(100vh - 7rem));"
                    >
                        <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Results {{ $year }}</div>
                        <div class="grid gap-1">
                            @foreach ($items as $item)
                                <a href="{{ route($item['route']) }}"
                                   class="{{ $routeName === $item['route'] ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-slate-50' }} rounded-xl px-3 py-2 text-sm transition-colors">
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            @if ($routeName === 'home')
                <a href="#predictors" class="bg-rose-500 hover:bg-rose-600 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all shadow-md shadow-rose-500/10 active:scale-95">
                    Start Predicting
                </a>
            @else
                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    NEET Results Portal
                </div>
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
                document.body.style.overflow = 'hidden';
                if (caret) caret.style.transform = 'rotate(180deg)';
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
