<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Educate - NEET Counselling Guidance & College Predictor</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
    @include('partials.anti-copy')
</head>

<body class="min-h-screen text-slate-800 flex flex-col bg-slate-50">

    @include('partials.results-header')

    @php
        $fallbackResultsUrl = auth()->check() ? route('dashboard') : route('register');
        $legacyResultUrl = fn (string $routeName) => Route::has($routeName) ? route($routeName) : $fallbackResultsUrl;
    @endphp

    <!-- Main Content -->
    <main class="flex-1">

        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-white via-slate-50 to-rose-50/30 py-20 lg:py-28 border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    
                    <!-- Left Column: Text & CTA -->
                    <div class="lg:col-span-6 space-y-6 text-left max-w-2xl mx-auto lg:mx-0">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-100 text-xs font-semibold text-rose-600">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                            </span>
                            NEET 2025 Counselling Live Predictor
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-tight">
                            Predict Your Best College for <span class="text-rose-500">NEET Guidance</span>
                        </h1>
                        <p class="text-slate-500 text-lg sm:text-xl font-normal leading-relaxed">
                            Personalized, data-backed guidance to secure your MBBS seat. Navigate rounds, verify state/quota closing ranks, and make your medical dream a reality.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 pt-2">
                            <a href="#predictors" class="px-8 py-4 bg-rose-500 hover:bg-rose-600 text-white font-bold text-base rounded-2xl transition-all shadow-lg hover:shadow-xl hover:shadow-rose-500/15 text-center flex items-center justify-center gap-2">
                                ⚡ Start Predictor Now
                            </a>
                            <a href="#about" class="px-8 py-4 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-base rounded-2xl transition-all text-center flex items-center justify-center">
                                Learn More
                            </a>
                        </div>
                        
                        <!-- Trust Indicators -->
                        <div class="pt-6 border-t border-slate-200/60 grid grid-cols-3 gap-4 text-left">
                            <div>
                                <div class="text-2xl font-bold text-slate-900">99.2%</div>
                                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Prediction Accuracy</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-slate-900">10k+</div>
                                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Students Assisted</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-slate-900">15+ Yrs</div>
                                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Expert Experience</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Interactive CSS Mockup -->
                    <div class="lg:col-span-6 relative w-full max-w-lg mx-auto lg:max-w-none">
                        <!-- Decorative glow -->
                        <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r from-rose-500 to-amber-500 opacity-20 blur-xl"></div>
                        
                        <!-- Portal Mockup Container -->
                        <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                            <!-- Mockup Header -->
                            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <div class="text-[11px] font-medium text-slate-400 tracking-wider uppercase bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700/50">
                                    <!-- careereducate.org/predictor -->
                                     rank.careereducate.com
                                </div>
                                <div class="w-4"></div>
                            </div>

                            <!-- Mockup Body -->
                            <div class="p-6 space-y-5 bg-slate-50">
                                <!-- Quick inputs -->
                                <div class="space-y-4">
                                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">All India Rank</div>
                                        <div class="text-lg font-bold text-slate-800 mt-1 flex justify-between items-center">
                                            <span>14,582</span>
                                            <span class="text-xs font-semibold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-md">Score: 638</span>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-white p-3.5 rounded-xl border border-slate-100 shadow-xs">
                                            <div class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Quota</div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">OPEN (General)</div>
                                        </div>
                                        <div class="bg-white p-3.5 rounded-xl border border-slate-100 shadow-xs">
                                            <div class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Round</div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Round 2</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Live Mock Results -->
                                <div class="space-y-3">
                                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Predicted Colleges</div>
                                    
                                    <!-- Eligible College Row 1 -->
                                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between hover:border-rose-100 transition-all">
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">Bangalore Medical College (BMCRI)</div>
                                            <div class="text-[10px] text-slate-400 font-semibold mt-0.5">State Quota • Government</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">Highly Eligible</div>
                                        </div>
                                    </div>

                                    <!-- Eligible College Row 2 -->
                                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between">
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">Mysore Medical College (MMCRI)</div>
                                            <div class="text-[10px] text-slate-400 font-semibold mt-0.5">State Quota • Government</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">Highly Eligible</div>
                                        </div>
                                    </div>

                                    <!-- Eligible College Row 3 -->
                                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between opacity-80">
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">ESIC Medical College, Bangalore</div>
                                            <div class="text-[10px] text-slate-400 font-semibold mt-0.5">All India Quota • Government</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-md">Moderate Chance</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Predictor Portal Section -->
        <section id="predictors" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Choose Your Predictor Portal
                    </h2>
                    <p class="text-slate-500 mt-3 text-base sm:text-lg">
                        Select a counselling engine based on year and quota to check eligible medical colleges.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- All India 2025 -->
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-rose-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-rose-500 text-white flex items-center justify-center font-bold text-lg mb-6 group-hover:scale-110 transition-transform">
                                IN
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">All India Quota 2025</h3>
                            <p class="text-slate-500 text-sm mt-2">
                                Predict MBBS admissions across government and central medical colleges in India under the 15% All India Quota.
                            </p>
                        </div>
                        <div class="mt-6">
                            <a href="{{ $legacyResultUrl('all-india-2025') }}" class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-rose-500/10">
                                Access Predictor ➔
                            </a>
                        </div>
                    </div>

                    <!-- Karnataka 2025 -->
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-rose-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg mb-6 group-hover:scale-110 transition-transform">
                                KA
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">Karnataka State 2025</h3>
                            <p class="text-slate-500 text-sm mt-2">
                                Predict MBBS seats in Karnataka state quota counseling (KEA) for government, private, and minority colleges.
                            </p>
                        </div>
                        <div class="mt-6">
                            <a href="{{ $legacyResultUrl('karnataka-2025') }}" class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-rose-500/10">
                                Access Predictor ➔
                            </a>
                        </div>
                    </div>

                    <!-- Karnataka 2024 -->
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-rose-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-slate-700 text-white flex items-center justify-center font-bold text-lg mb-6 group-hover:scale-110 transition-transform">
                                KA
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">Karnataka State 2024</h3>
                            <p class="text-slate-500 text-sm mt-2">
                                View official historical cutoffs and closing ranks from KEA 2024 counseling rounds to analyze seat allotment trends.
                            </p>
                        </div>
                        <div class="mt-6">
                            <a href="{{ $legacyResultUrl('karnataka-2024') }}" class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl transition-all">
                                View 2024 Cutoffs ➔
                            </a>
                        </div>
                    </div>

                    <!-- Karnataka 2023 -->
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-rose-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-slate-600 text-white flex items-center justify-center font-bold text-lg mb-6 group-hover:scale-110 transition-transform">
                                KA
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">Karnataka State 2023</h3>
                            <p class="text-slate-500 text-sm mt-2">
                                Examine 2023 round-by-round seat allocation data to gain deeper insights into long-term college allotment patterns.
                            </p>
                        </div>
                        <div class="mt-6">
                            <a href="{{ $legacyResultUrl('karnataka-2023') }}" class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl transition-all">
                                View 2023 Cutoffs ➔
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Proven Mentorship / core features -->
        <section id="about" class="py-20 bg-slate-50 border-t border-b border-slate-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Proven Mentorship. Success Today, Ranked Tomorrow.
                    </h2>
                    <p class="text-slate-500 mt-3 text-base sm:text-lg">
                        Our predictor tools process millions of data points to bring you the most accurate mapping of medical college admissions.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 font-bold mb-6">
                                📊
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Round-Wise Analysis</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                Tracks changes in cutoff rank trends from Round 1, Round 2, Mop-Up and Stray Vacancy rounds to help you strategize choice-filling.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 font-bold mb-6">
                                🛡️
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Category & Quota Safeguards</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                Filters specifically by your social category (OBC, SC, ST, EWS, Gen) and sub-quotas to show only relevant, achievable cutoff targets.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 font-bold mb-6">
                                💰
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Tuition Fee Slider</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                Narrow down colleges based on your budget. Filter out institutes that exceed your tuition capacity with real-time fee details.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Section: Choice Filling Strategy (Dream-Realistic-Safe) -->
        <section class="py-20 bg-white border-b border-slate-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-rose-500 font-bold text-xs uppercase tracking-widest bg-rose-50 px-3 py-1.5 rounded-full border border-rose-100">Counselling Secret</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-4">
                        The "Dream-Realistic-Safe" Choice Filling Strategy
                    </h2>
                    <p class="text-slate-500 mt-3 text-base sm:text-lg">
                        Since seat allotment algorithms process lists in strict priority order, always arrange your preferences using this three-tier optimization framework.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Tier 1 -->
                    <div class="bg-gradient-to-br from-slate-50 to-rose-50/20 p-8 rounded-3xl border border-slate-200 hover:border-rose-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <span class="bg-rose-100 text-rose-700 font-bold text-xs px-3 py-1.5 rounded-lg">Tier 1 • Top 20%</span>
                                <span class="text-2xl">🌟</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Dream Choices (Aspirational)</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                Put your absolute top-choice colleges at the very top of your preference list. Even if your rank is slightly below their historical cutoff, placing them first ensures you are considered if cutoffs drop.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-200/60 text-xs text-rose-500 font-semibold">
                            Example: Top Government Medical Colleges
                        </div>
                    </div>

                    <!-- Tier 2 -->
                    <div class="bg-gradient-to-br from-slate-50 to-amber-50/20 p-8 rounded-3xl border border-slate-200 hover:border-amber-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <span class="bg-amber-100 text-amber-800 font-bold text-xs px-3 py-1.5 rounded-lg">Tier 2 • Mid 60%</span>
                                <span class="text-2xl">🎯</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Realistic Choices (Target)</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                Add colleges where your NEET rank sits comfortably within or near the 3-year historical closing ranks. These are your primary target seats where allotment is highly competitive but highly realistic.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-200/60 text-xs text-amber-600 font-semibold">
                            Example: State & AIQ matching ranks
                        </div>
                    </div>

                    <!-- Tier 3 -->
                    <div class="bg-gradient-to-br from-slate-50 to-emerald-50/20 p-8 rounded-3xl border border-slate-200 hover:border-emerald-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <span class="bg-emerald-100 text-emerald-800 font-bold text-xs px-3 py-1.5 rounded-lg">Tier 3 • Bottom 20%</span>
                                <span class="text-2xl">🛡️</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Safe Choices (Backup)</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                Always include "safety net" options at the end of your list—colleges where closing ranks are much higher than your score. This guarantees you do not get knocked out of counseling without a seat.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-200/60 text-xs text-emerald-600 font-semibold">
                            Example: Secure Government/Private seats
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Section: Round-by-Round Roadmap Timeline -->
        <section id="roadmap" class="py-20 bg-slate-50 border-b border-slate-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-rose-500 font-bold text-xs uppercase tracking-widest bg-rose-50 px-3 py-1.5 rounded-full border border-rose-100">Counselling Cycle</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-4">
                        Round-by-Round Counselling Roadmap
                    </h2>
                    <p class="text-slate-500 mt-3 text-base sm:text-lg">
                        NEET counseling is a multi-step tournament. Learn the rules, upgrade parameters, and forfeiture conditions for each round.
                    </p>
                </div>

                <!-- Horizontal/Vertical Timeline Wrapper -->
                <div class="relative max-w-5xl mx-auto">
                    <!-- Vertical line for mobile, horizontal line hidden -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-rose-200 md:hidden"></div>
                    
                    <!-- Grid Layout for timeline stages -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-4 relative">
                        
                        <!-- Step 1 -->
                        <div class="relative pl-10 md:pl-0 md:pt-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm md:bg-transparent md:border-none md:shadow-none hover:translate-y-[-4px] transition-transform">
                            <!-- Bullet point locator -->
                            <div class="absolute left-2.5 top-8 md:left-1/2 md:top-0 md:-translate-x-1/2 md:-translate-y-1/2 w-4 h-4 rounded-full bg-rose-500 border-4 border-white shadow-md z-10"></div>
                            <div class="text-rose-500 font-bold text-xs uppercase tracking-wider mb-2">Round 1</div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Free Exit Allocation</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">
                                Candidates can exit freely if allocated a seat in Round 1. Security deposit is fully refunded, and they can register for subsequent rounds without penalty.
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="relative pl-10 md:pl-0 md:pt-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm md:bg-transparent md:border-none md:shadow-none hover:translate-y-[-4px] transition-transform">
                            <!-- Bullet point locator -->
                            <div class="absolute left-2.5 top-8 md:left-1/2 md:top-0 md:-translate-x-1/2 md:-translate-y-1/2 w-4 h-4 rounded-full bg-rose-500 border-4 border-white shadow-md z-10"></div>
                            <div class="text-rose-500 font-bold text-xs uppercase tracking-wider mb-2">Round 2</div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Commitment Allotment</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">
                                If you exit Round 2 after allotment, your security deposit is forfeited. To hold the seat and seek upgrade, you must report physically.
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="relative pl-10 md:pl-0 md:pt-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm md:bg-transparent md:border-none md:shadow-none hover:translate-y-[-4px] transition-transform">
                            <!-- Bullet point locator -->
                            <div class="absolute left-2.5 top-8 md:left-1/2 md:top-0 md:-translate-x-1/2 md:-translate-y-1/2 w-4 h-4 rounded-full bg-rose-500 border-4 border-white shadow-md z-10"></div>
                            <div class="text-rose-500 font-bold text-xs uppercase tracking-wider mb-2">Round 3 (Mop-Up)</div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Fresh Registration</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">
                                Fresh registrations are allowed. If allocated a seat in Round 3, you cannot exit. If you refuse the seat, you are barred from state counseling.
                            </p>
                        </div>

                        <!-- Step 4 -->
                        <div class="relative pl-10 md:pl-0 md:pt-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm md:bg-transparent md:border-none md:shadow-none hover:translate-y-[-4px] transition-transform">
                            <!-- Bullet point locator -->
                            <div class="absolute left-2.5 top-8 md:left-1/2 md:top-0 md:-translate-x-1/2 md:-translate-y-1/2 w-4 h-4 rounded-full bg-rose-500 border-4 border-white shadow-md z-10"></div>
                            <div class="text-rose-500 font-bold text-xs uppercase tracking-wider mb-2">Stray Vacancy</div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Spot Allocation</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">
                                Offline spot rounds conducted directly by colleges for vacant seats. No fresh choice registration is allowed. Only choices entered in Round 3 are considered.
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        <!-- Section: Bond & Stipend Informational Highlights -->
        <section class="py-20 bg-white border-b border-slate-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Block: Detailed Content -->
                    <div class="lg:col-span-5 space-y-6 text-left">
                        <span class="text-rose-500 font-bold text-xs uppercase tracking-widest bg-rose-50 px-3 py-1.5 rounded-full border border-rose-100">Crucial Details</span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Verify Service Bonds & Stipends Before Locking
                        </h2>
                        <p class="text-slate-500 text-sm sm:text-base leading-relaxed">
                            Counselling is not just about rankings. Service bonds can bind you to mandatory rural service after graduation, and stipend values vary vastly by college and region.
                        </p>
                        <div class="bg-rose-50/50 p-6 rounded-2xl border border-rose-100/50 space-y-4">
                            <div class="flex gap-3 items-start">
                                <span class="text-rose-500 text-lg">💡</span>
                                <p class="text-xs text-rose-700 leading-relaxed">
                                    <strong>Expert Advice:</strong> Karnataka Government Medical Colleges have a mandatory 1-year service bond with penalties up to ₹10 Lakhs if violated.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Block: Grid of Bond & Stipend metrics -->
                    <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Bond Details Card -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-3">
                            <div class="text-2xl">📜</div>
                            <h4 class="font-bold text-slate-900 text-base">Service Bond Duration</h4>
                            <p class="text-slate-500 text-xs leading-relaxed">
                                Ranges from 1 year (Karnataka) to 5 years (some central institutes). Failure to complete service requires payment of penalty amounts.
                            </p>
                        </div>

                        <!-- Penalty Card -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-3">
                            <div class="text-2xl">⚠️</div>
                            <h4 class="font-bold text-slate-900 text-base">Bond Penalty Fees</h4>
                            <p class="text-slate-500 text-xs leading-relaxed">
                                Violation penalties range from ₹1 Lakh to ₹50 Lakhs. Make sure to examine bond clauses before submitting final choice entries.
                            </p>
                        </div>

                        <!-- Stipend Card -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-3">
                            <div class="text-2xl">💸</div>
                            <h4 class="font-bold text-slate-900 text-base">Internship Stipends</h4>
                            <p class="text-slate-500 text-xs leading-relaxed">
                                Monthly pay ranges from ₹20,000 (standard state colleges) to ₹30,000+ (central colleges) during your mandatory internship year.
                            </p>
                        </div>

                        <!-- PG Stipends Card -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-3">
                            <div class="text-2xl">📈</div>
                            <h4 class="font-bold text-slate-900 text-base">PG stipend allowances</h4>
                            <p class="text-slate-500 text-xs leading-relaxed">
                                During PG (MD/MS) residency, stipends can range from ₹50,000 to ₹1 Lakh/month, acting as crucial financial support.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Alumni Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Some of our Proud Alumni
                    </h2>
                    <p class="text-slate-500 mt-3 text-base sm:text-lg">
                        Thousands of students assist by Career Educate are now studying in India's top-tier medical colleges.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Alumni 1 -->
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-slate-300 flex items-center justify-center text-slate-700 font-bold text-base">
                                    SK
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">Suhail Khan</div>
                                    <div class="text-xs text-rose-500 font-medium">NEET Rank: 8,421</div>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm italic">
                                "The rank predictor accurately showed that Bangalore Medical College was within reach in Round 2. It saved me a lot of stress during the counselling choice entry process."
                            </p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-200/50 text-xs font-semibold text-slate-400">
                            Admitted to BMCRI, Bangalore
                        </div>
                    </div>

                    <!-- Alumni 2 -->
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-slate-300 flex items-center justify-center text-slate-700 font-bold text-base">
                                    AP
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">Ananya Patel</div>
                                    <div class="text-xs text-rose-500 font-medium">NEET Rank: 14,210</div>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm italic">
                                "Comparing Karnataka state quota trends for 2023 and 2024 side-by-side using the portal was the key to my counselling strategy. Highly recommended predictor."
                            </p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-200/50 text-xs font-semibold text-slate-400">
                            Admitted to MMCRI, Mysore
                        </div>
                    </div>

                    <!-- Alumni 3 -->
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-slate-300 flex items-center justify-center text-slate-700 font-bold text-base">
                                    RM
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">Rahul Mishra</div>
                                    <div class="text-xs text-rose-500 font-medium">NEET Rank: 19,510</div>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm italic">
                                "I was confused between ESIC and private minority seats. Career Educate's fee filters and round-wise charts gave me exact clarity on what I could afford and get."
                            </p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-200/50 text-xs font-semibold text-slate-400">
                            Admitted to KIMS, Hubli
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Interactive FAQ Accordion -->
        <section class="py-20 bg-slate-50 border-t border-slate-200/60">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Frequently Asked Questions
                    </h2>
                    <p class="text-slate-500 mt-3 text-base">
                        Get answers to the most common queries regarding NEET quota counselling.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- FAQ 1 -->
                    <details class="group bg-white rounded-2xl border border-slate-200 p-6 [&_summary::-webkit-details-marker]:hidden cursor-pointer shadow-sm">
                        <summary class="flex justify-between items-center text-slate-900 font-bold text-base sm:text-lg">
                            <span>How accurate is the NEET rank predictor?</span>
                            <span class="transition group-open:rotate-180 text-rose-500">
                                🧭
                            </span>
                        </summary>
                        <p class="text-slate-500 text-sm leading-relaxed mt-4">
                            Our rank predictor is built directly using the official MCC and KEA seat allotment data from previous years. It accounts for categories, quotas, and specific counseling rounds, resulting in over 99% accuracy in eligible college listings.
                        </p>
                    </details>

                    <!-- FAQ 2 -->
                    <details class="group bg-white rounded-2xl border border-slate-200 p-6 [&_summary::-webkit-details-marker]:hidden cursor-pointer shadow-sm">
                        <summary class="flex justify-between items-center text-slate-900 font-bold text-base sm:text-lg">
                            <span>What is the difference between All India Quota and State Quota?</span>
                            <span class="transition group-open:rotate-180 text-rose-500">
                                🧭
                            </span>
                        </summary>
                        <p class="text-slate-500 text-sm leading-relaxed mt-4">
                            All India Quota (15% of government medical college seats) is open to students nationwide and counselling is conducted by MCC. State Quota (85% of government seats and 100% of state private college seats) is reserved for native residents of the respective state and conducted by state bodies like KEA.
                        </p>
                    </details>

                    <!-- FAQ 3 -->
                    <details class="group bg-white rounded-2xl border border-slate-200 p-6 [&_summary::-webkit-details-marker]:hidden cursor-pointer shadow-sm">
                        <summary class="flex justify-between items-center text-slate-900 font-bold text-base sm:text-lg">
                            <span>Does the tuition fee data include other hidden charges?</span>
                            <span class="transition group-open:rotate-180 text-rose-500">
                                🧭
                            </span>
                        </summary>
                        <p class="text-slate-500 text-sm leading-relaxed mt-4">
                            The fees listed represent the standard academic tuition fee per annum. Miscellaneous charges like hostel fees, security deposits, and university exam fees might be charged separately by individual colleges.
                        </p>
                    </details>
                </div>

            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 text-xs py-12 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('career.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                        <!-- <span class="font-bold text-base text-white tracking-tight">Career Educate</span> -->
                    </div>
                    <p class="text-slate-500 text-xs leading-relaxed">
                        Data-backed guidance solutions to empower medical aspirants across India. We turn NEET aspirations into successful doctor careers.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-4">Predictor Portals</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ $legacyResultUrl('all-india-2025') }}" class="hover:text-white transition-colors">All India Quota 2025</a></li>
                        <li><a href="{{ $legacyResultUrl('karnataka-2025') }}" class="hover:text-white transition-colors">Karnataka State 2025</a></li>
                        <li><a href="{{ $legacyResultUrl('karnataka-2024') }}" class="hover:text-white transition-colors">Karnataka State 2024</a></li>
                        <li><a href="{{ $legacyResultUrl('karnataka-2023') }}" class="hover:text-white transition-colors">Karnataka State 2023</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Support</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Mentorship Program</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-4">Contact Info</h4>
                    <div class="space-y-4 text-slate-500 text-xs leading-relaxed">
                        <div>
                            <div class="font-bold text-slate-300">Hyderabad Office</div>
                            <p>
                                Career Educate<br>
                                8-1-347/B1/A &amp; 8-1-348/A<br>
                                Above Ratandeep Super Market, Shaikpet Road,<br>
                                Tolichowki, Hyderabad, Telangana - 500001<br>
                                Contact: 8951918163
                            </p>
                        </div>
                        <div>
                            <div class="font-bold text-slate-300">Bangalore Office</div>
                            <p>
                                Career Educate<br>
                                #5 GC Rich Homes, Richmond Road,<br>
                                Bangalore - 560025<br>
                                Contact: 7760303019<br>
                                <a href="https://maps.app.goo.gl/rFLfaxVxjywNGYGN8" target="_blank" rel="noopener" class="text-rose-400 hover:text-rose-300">View Location</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    version 2.2.9 | &copy; 2026 Career Educate. All rights reserved.
                </div>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-white transition-colors">Terms and Conditions</a>
                    <span>|</span>
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
