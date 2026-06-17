<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    @php
        $user = $user ?? auth()->user();
        $planLabel = $user->plan && $user->plan !== 'none' ? ucfirst($user->plan) : 'Active';
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Student Dashboard</p>
                    <h1 class="mt-2 text-3xl font-extrabold text-slate-950">Welcome, {{ $user->name }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Your account is ready. Use your saved rank, state, and quota defaults to explore available NEET result pages faster.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if ($latestMbbs)
                        <a href="{{ route('results.show', $latestMbbs) }}" class="rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-600">
                            Open Latest MBBS
                        </a>
                    @endif
                    @if ($latestBds)
                        <a href="{{ route('results.show', $latestBds) }}" class="rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                            Open Latest BDS
                        </a>
                    @endif
                    <a href="{{ route('profile') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                        Edit Profile
                    </a>
                    <a href="{{ route('neet.analysis') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                        NEET UG Analysis
                    </a>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-5 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Subscription</p>
                <div class="mt-3 text-2xl font-extrabold text-slate-950">{{ $planLabel }}</div>
                <p class="mt-1 text-xs text-emerald-600">Payment verified</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Profile Completion</p>
                <div class="mt-3 text-2xl font-extrabold text-slate-950">{{ $profileCompletion }}%</div>
                <div class="mt-3 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-rose-500" style="width: {{ $profileCompletion }}%"></div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Result Pages</p>
                <div class="mt-3 text-2xl font-extrabold text-slate-950">{{ number_format($datasetCount) }}</div>
                <p class="mt-1 text-xs text-slate-400">{{ number_format($availableYears) }} active NEET years</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Rank Records</p>
                <div class="mt-3 text-2xl font-extrabold text-slate-950">{{ number_format($recordCount) }}</div>
                <p class="mt-1 text-xs text-slate-400">Imported counselling rows</p>
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Your Saved Criteria</h2>
                <p class="mt-1 text-xs text-slate-500">These defaults help you start result filtering with less typing.</p>
                <div class="mt-5 grid gap-3 text-sm">
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                        <span class="font-semibold text-slate-500">AIR</span>
                        <span class="font-bold text-slate-950">{{ $user->neet_rank ? number_format($user->neet_rank) : 'Not set' }}</span>
                    </div>
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                        <span class="font-semibold text-slate-500">Marks</span>
                        <span class="font-bold text-slate-950">{{ $user->neet_marks !== null ? rtrim(rtrim((string) $user->neet_marks, '0'), '.') : 'Not set' }}</span>
                    </div>
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                        <span class="font-semibold text-slate-500">State</span>
                        <span class="font-bold text-slate-950 text-right">{{ $user->state ?: 'Not set' }}</span>
                    </div>
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                        <span class="font-semibold text-slate-500">Quota</span>
                        <span class="font-bold text-slate-950 text-right">{{ $user->quota ?: 'Not set' }}</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Available Result Pages</h2>
                        <p class="mt-1 text-xs text-slate-500">Latest active datasets from the imported result library.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @forelse ($datasets as $dataset)
                        <a href="{{ route('results.show', $dataset) }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-rose-300 hover:bg-white hover:shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-slate-950 group-hover:text-rose-600">{{ $dataset->title }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        {{ $dataset->course ?? 'NEET' }} | {{ $dataset->year ?? 'Dynamic' }}
                                    </p>
                                </div>
                                <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-500 ring-1 ring-slate-200">
                                    {{ number_format($dataset->rank_records_count) }} rows
                                </span>
                            </div>
                            <p class="mt-3 text-xs font-bold text-rose-500">Open result page</p>
                        </a>
                    @empty
                        <div class="md:col-span-2 rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm font-semibold text-slate-400">
                            No active result pages are available yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-600">NEET UG 2025 Analysis</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-slate-950">View National Candidate, Seat, and College Analytics</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Includes the same content from the Karnataka PPT: candidate overview, category funnel, year-wise analysis, qualified performance, seats across India, and college type comparison.
                    </p>
                </div>
                <a href="{{ route('neet.analysis') }}" class="inline-flex shrink-0 justify-center rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-600">
                    Open Analysis
                </a>
            </div>
        </section>
    </main>
</body>

</html>
