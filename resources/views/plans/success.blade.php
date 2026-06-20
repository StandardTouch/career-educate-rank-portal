<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
    @include('partials.anti-copy')
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden p-8 text-center space-y-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full border border-emerald-100 shadow-md">
                <svg class="w-10 h-10 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <div class="space-y-2">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600">Transaction Approved</p>
                <h1 class="text-3xl font-extrabold text-slate-950">Payment Success!</h1>
                <p class="text-sm text-slate-500">Thank you. Your account features have been successfully unlocked.</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-5 text-left text-sm space-y-3">
                <div class="flex justify-between">
                    <span class="font-medium text-slate-500">Plan Selected</span>
                    <span class="font-bold text-slate-900 capitalize">{{ $plan }} Support</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-slate-500">Amount Paid</span>
                    <span class="font-extrabold text-rose-500">₹{{ $plan === 'basic' ? '2,000' : '5,000' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-slate-500">Status</span>
                    <span class="font-bold text-emerald-600">Active</span>
                </div>
            </div>

            <div class="pt-4">
                <a href="{{ route('dashboard') }}" class="block w-full text-center rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                    Open Predictor Dashboard
                </a>
            </div>
        </section>
    </main>
</body>

</html>
