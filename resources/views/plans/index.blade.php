<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

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

<body class="min-h-screen bg-slate-50 text-slate-950">
    @include('partials.results-header')

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        @if (session('warning'))
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                {{ session('warning') }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white px-5 py-6 shadow-sm sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Subscription Required</p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                        Choose Your NEET Counselling Plan
                    </h1>
                    <p class="mt-3 text-base leading-7 text-slate-600">
                        Complete your subscription to unlock the predictor, result filters, and counselling support for your admission process.
                    </p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <span class="font-bold text-slate-950">Mobile verified</span>
                    <span class="mx-2 text-slate-300">|</span>
                    Next step: select a plan
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-5 lg:grid-cols-2">
            <article class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-600">Self Service</span>
                        <h2 class="mt-4 text-2xl font-extrabold text-slate-950">Counselling Information & Web Access Support</h2>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-extrabold text-slate-950">Rs. 2,000</div>
                        <div class="text-sm font-medium text-slate-500">registration fee</div>
                    </div>
                </div>

                <p class="mt-4 text-sm leading-6 text-slate-600">
                    Essential digital utilities and alerts for students managing their own admissions.
                </p>

                <ul class="mt-6 grid gap-3 border-t border-slate-100 pt-5 text-sm font-medium text-slate-700">
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>Access to counselling information</span></li>
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>Registration guidance</span></li>
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>Official website navigation support</span></li>
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>Basic counselling updates and alerts</span></li>
                </ul>

                <a href="{{ route('plans.checkout', 'basic') }}" class="mt-7 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 transition hover:border-rose-300 hover:text-rose-600 active:scale-95">
                    Choose Basic Plan
                </a>
            </article>

            <article class="flex h-full flex-col rounded-2xl border border-rose-200 bg-white p-6 shadow-sm ring-1 ring-rose-100">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-rose-600">Recommended</span>
                        <h2 class="mt-4 text-2xl font-extrabold text-slate-950">Complete Counselling Guidance</h2>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-extrabold text-slate-950">Rs. 5,000</div>
                        <div class="text-sm font-medium text-slate-500">service fee</div>
                    </div>
                </div>

                <p class="mt-4 text-sm leading-6 text-slate-600">
                    End-to-end guidance with dedicated phone and physical support throughout counselling.
                </p>

                <ul class="mt-6 grid gap-3 border-t border-slate-100 pt-5 text-sm font-medium text-slate-700 sm:grid-cols-2">
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>End-to-end counselling guidance</span></li>
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>College selection assistance</span></li>
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>Choice filling strategy</span></li>
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>Document verification guidance</span></li>
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>Phone support throughout counselling</span></li>
                    <li class="flex gap-3"><span class="font-bold text-rose-500">&check;</span><span>Seat allotment and reporting guidance</span></li>
                </ul>

                <a href="{{ route('plans.checkout', 'premium') }}" class="mt-7 inline-flex w-full items-center justify-center rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition hover:bg-rose-600 active:scale-95">
                    Choose Premium Plan
                </a>
            </article>
        </section>
    </main>
</body>

</html>
