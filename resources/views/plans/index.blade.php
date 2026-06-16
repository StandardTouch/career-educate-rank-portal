<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselling Packages - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>

<body class="min-h-screen bg-slate-900 text-slate-100 pb-16">
    @include('partials.results-header')

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        @if (session('warning'))
            <div class="mb-8 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">
                {{ session('warning') }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-8 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="text-center max-w-2xl mx-auto">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Premium Packages</p>
            <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                NEET Counselling Guidance Services & Fee Structure
            </h1>
            <p class="mt-4 text-base text-slate-400">
                Unlock instant access to the dynamic predictor tool and receive professional support for your college admissions.
            </p>
        </div>

        <div class="mt-16 grid gap-8 md:grid-cols-2 max-w-4xl mx-auto">
            <!-- Package 1: Web Access Support -->
            <article class="relative flex flex-col justify-between rounded-3xl border border-slate-800 bg-slate-950 p-8 shadow-2xl hover:border-slate-700 transition duration-300 group">
                <div class="absolute -top-3 right-6 rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-300 border border-slate-700">
                    Self Service
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">1. Counselling Information & Web Access Support</h2>
                    <p class="mt-2 text-sm text-slate-400">Essential digital utilities and alerts for self-managed admissions.</p>
                    
                    <div class="mt-6 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-white">₹2,000</span>
                        <span class="text-sm text-slate-400">registration fee</span>
                    </div>

                    <ul class="mt-8 space-y-4 text-sm text-slate-300 border-t border-slate-900 pt-6">
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>Access to counselling information</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>Registration guidance</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>Official website navigation support</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>Basic counselling updates and alerts</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-8 pt-6">
                    <a href="{{ route('plans.checkout', 'basic') }}" class="block w-full text-center rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-700 active:scale-95 group-hover:bg-rose-500">
                        Choose Basic Plan
                    </a>
                </div>
            </article>

            <!-- Package 2: Phone & Physical Support -->
            <article class="relative flex flex-col justify-between rounded-3xl border border-rose-500/30 bg-slate-950 p-8 shadow-2xl hover:border-rose-500/50 transition duration-300 group">
                <div class="absolute -top-3 right-6 rounded-full bg-rose-500/20 px-3 py-1 text-xs font-semibold text-rose-300 border border-rose-500/30">
                    Recommended
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">2. Complete Counselling Guidance (Phone & Physical Support)</h2>
                    <p class="mt-2 text-sm text-slate-400">End-to-end guidance with dedicated mentor support throughout the process.</p>
                    
                    <div class="mt-6 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-white">₹5,000</span>
                        <span class="text-sm text-slate-400">service fee</span>
                    </div>

                    <ul class="mt-8 space-y-4 text-sm text-slate-300 border-t border-slate-900 pt-6">
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span class="font-semibold text-white">End-to-end counselling guidance</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>College selection assistance</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>Choice filling strategy</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>Document verification guidance</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>Phone support throughout counselling</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>In-person (physical) counselling assistance</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold">✓</span>
                            <span>Seat allotment and reporting guidance</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-8 pt-6">
                    <a href="{{ route('plans.checkout', 'premium') }}" class="block w-full text-center rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition hover:bg-rose-600 active:scale-95">
                        Choose Premium Plan
                    </a>
                </div>
            </article>
        </div>
    </main>
</body>

</html>
