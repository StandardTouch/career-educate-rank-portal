<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Admin Dashboard</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Portal Management</h1>
            </div>
            <a href="{{ route('import.excel') }}" class="inline-flex justify-center rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                Import Excel
            </a>
        </div>

        <section class="mt-8 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">Result Pages</div>
                <div class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($pageCount) }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">Result Years</div>
                <div class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($yearCount) }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">Users</div>
                <div class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($userCount) }}</div>
            </div>
        </section>

        <section class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-950">Recent Menu Pages</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($latestPages as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50">
                        <div>
                            <div class="font-semibold text-slate-800">{{ $item['label'] }}</div>
                            <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $item['group'] }}</div>
                        </div>
                        <span class="text-sm font-bold text-rose-500">Open</span>
                    </a>
                @empty
                    <div class="px-6 py-8 text-sm text-slate-500">No menu pages found.</div>
                @endforelse
            </div>
        </section>
    </main>
</body>

</html>
