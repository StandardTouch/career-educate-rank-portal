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
        $fallbackResultsUrl = route('dashboard');
        $legacyResultUrl = fn (string $routeName) => Route::has($routeName) ? route($routeName) : $fallbackResultsUrl;
    @endphp

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Student Dashboard</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">Welcome, {{ auth()->user()->name }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                Your mobile number is verified. You now have access to the NEET result pages and predictor tools.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ $legacyResultUrl('all-over-india-data-mbbs-2025') }}" class="rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-600">
                    Open NEET 2025 MBBS
                </a>
                <a href="{{ $legacyResultUrl('all-indida-quota-bds-2025') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                    Open NEET 2025 BDS
                </a>
            </div>
        </section>
    </main>
</body>

</html>
