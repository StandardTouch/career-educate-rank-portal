<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50/50">

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
    @include('partials.anti-copy')
</head>

<body class="min-h-screen bg-slate-50/50 text-slate-800 antialiased">
    @include('partials.results-header')

    @php
        $successRate = $importCount > 0 ? round(($completedImportCount / $importCount) * 100) : 0;
        $verifiedRate = $userCount > 0 ? round(($verifiedMobileCount / $userCount) * 100) : 0;
        $paidRate = $studentCount > 0 ? round(($paidUserCount / $studentCount) * 100) : 0;
        $paymentSuccessRate = $paymentCount > 0 ? round(($completedPaymentCount / $paymentCount) * 100) : 0;
        $maxYearCount = max(1, (int) $datasetsByYear->max('count'));
        $maxCourseCount = max(1, (int) $datasetsByCourse->max('count'));
        $maxDatasetRecords = max(1, (int) $topDatasets->max('rank_records_count'));
        $maxCategoryRegistered = max(1, (int) $categoryFunnel->max('registered'));
        $maxSeatIncrease = max(1, (int) $seatGrowthStates->max('increase'));
        $maxCollegeTypeCount = max(1, (int) $collegeTypeComparison->max('2025'));
        $latestResultDataset = $latestImport?->dataset ?? $topDatasets->first();
    @endphp

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-20">
        <!-- Header Section -->
        <section class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between border-b border-slate-200/60 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-bold text-rose-600 ring-1 ring-inset ring-rose-500/10">System Admin</span>
                    <span class="text-xs font-semibold text-slate-400">• Control Center</span>
                </div>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Portal Analytics</h1>
                <p class="mt-1 text-sm text-slate-500 max-w-2xl">
                    Monitor imported datasets, result coverage, user access, subscriptions, and payment health from one operational view.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.users') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 active:scale-98">
                    Users
                </a>
                <a href="{{ route('admin.payments') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 active:scale-98">
                    Payments
                </a>
                @if ($latestResultDataset)
                    <a href="{{ route('results.show', $latestResultDataset) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 active:scale-98">
                        <span>Open Latest Result</span>
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                @else
                    <span class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-2.5 text-sm font-semibold text-slate-400 cursor-not-allowed">
                        <span>Open Latest Result</span>
                    </span>
                @endif
                <a href="{{ route('import.excel') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/10 transition-all duration-200 hover:bg-rose-600 hover:shadow-rose-600/20 active:scale-98">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Import Excel</span>
                </a>
            </div>
        </section>

        <!-- Stats Grid -->
        <section class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Result Pages -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300/80">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-rose-50 p-2.5 text-rose-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500">Result Pages</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-600 ring-1 ring-inset ring-rose-500/10">{{ number_format($yearCount) }} yrs</span>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-bold tracking-tight text-slate-900">{{ number_format($pageCount) }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Active dynamic pages plus legacy configured items.</p>
            </div>

            <!-- Rank Records -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300/80">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-emerald-50 p-2.5 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500">Rank Records</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-600 ring-1 ring-inset ring-emerald-500/10">{{ number_format($sheetCount) }} sheets</span>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-bold tracking-tight text-slate-900">{{ number_format($recordCount) }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ number_format($roundCount) }} round sheets available for filters.</p>
            </div>

            <!-- Import Success -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300/80">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-violet-50 p-2.5 text-violet-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500">Import Success</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ number_format($importCount) }} total</span>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span class="text-3xl font-bold tracking-tight text-slate-900">{{ $successRate }}%</span>
                    <span class="text-[11px] font-medium text-slate-400">{{ number_format($completedImportCount) }} ok / {{ number_format($failedImportCount) }} fail</span>
                </div>
                <div class="mt-3.5 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-violet-600 transition-all duration-500" style="width: {{ $successRate }}%"></div>
                </div>
            </div>

            <!-- Users -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300/80">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-blue-50 p-2.5 text-blue-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0 1 10.089 21c-2.9 0-5.54-.822-7.77-2.25m17.965-3.375a9.775 9.775 0 0 0-1.323-4.37M19.122 19.1c.14-.308.22-.647.22-1.007a5.002 5.002 0 0 0-4.75-4.992M18.02 9.75a3.25 3.25 0 1 1-6.5 0 3.25 3.25 0 0 1 6.5 0ZM12.75 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0M9.75 9.75a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500">Users</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-600 ring-1 ring-inset ring-blue-500/10">{{ number_format($adminCount) }} admins</span>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span class="text-3xl font-bold tracking-tight text-slate-900">{{ number_format($userCount) }}</span>
                    <span class="text-[11px] font-medium text-slate-400">{{ $verifiedRate }}% verified</span>
                </div>
                <div class="mt-3.5 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: {{ $verifiedRate }}%"></div>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Revenue</p>
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">{{ number_format($completedPaymentCount) }} paid</span>
                </div>
                <div class="mt-4 text-3xl font-extrabold text-slate-950">Rs. {{ number_format($totalRevenue, 2) }}</div>
                <p class="mt-2 text-xs text-slate-400">{{ number_format($paymentCount) }} transactions, {{ number_format($pendingPaymentCount) }} pending.</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Subscriptions</p>
                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">{{ $paidRate }}% paid</span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <div class="text-xl font-extrabold text-slate-950">{{ number_format($paidUserCount) }}</div>
                        <div class="text-[10px] font-bold uppercase text-slate-400">Paid</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <div class="text-xl font-extrabold text-slate-950">{{ number_format($basicPlanCount) }}</div>
                        <div class="text-[10px] font-bold uppercase text-slate-400">Basic</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <div class="text-xl font-extrabold text-slate-950">{{ number_format($premiumPlanCount) }}</div>
                        <div class="text-[10px] font-bold uppercase text-slate-400">Premium</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Payment Health</p>
                    <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-bold text-violet-700">{{ $paymentSuccessRate }}% success</span>
                </div>
                <div class="mt-4 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-violet-600" style="width: {{ $paymentSuccessRate }}%"></div>
                </div>
                <p class="mt-4 text-xs text-slate-400">{{ number_format($unpaidUserCount) }} users still unpaid or pending plan selection.</p>
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Recent Users</h2>
                        <p class="text-[11px] text-slate-400">Newest registrations and admin accounts.</p>
                    </div>
                    <a href="{{ route('admin.users') }}" class="text-xs font-bold text-rose-500 hover:text-rose-600">View all</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($recentUsers as $user)
                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $user->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $user->email }} {{ $user->phone ? ' | ' . $user->phone : '' }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $user->is_admin ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600' }}">{{ $user->is_admin ? 'Admin' : 'Student' }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $user->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($user->payment_status ?? 'unpaid') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-xs font-semibold text-slate-400">No users found.</div>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Recent Payments</h2>
                        <p class="text-[11px] text-slate-400">Latest Razorpay transactions.</p>
                    </div>
                    <a href="{{ route('admin.payments') }}" class="text-xs font-bold text-rose-500 hover:text-rose-600">View all</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($recentPayments as $payment)
                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $payment->user?->name ?? 'Deleted user' }}</p>
                                <p class="truncate text-xs text-slate-500">{{ ucfirst($payment->plan) }} | {{ $payment->transaction_id ?? $payment->order_id ?? 'No transaction id' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-extrabold text-slate-900">Rs. {{ number_format((float) $payment->amount, 2) }}</p>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ in_array($payment->status, ['completed', 'captured'], true) ? 'bg-emerald-50 text-emerald-700' : ($payment->status === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($payment->status) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-xs font-semibold text-slate-400">No payments found.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-8 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">NEET UG 2025 Analytics</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-slate-950">National Admission Intelligence</h2>
                    <p class="mt-1 text-sm text-slate-500">At-a-glance counselling indicators inspired by the client presentation format.</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Reference dashboard view</span>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($neetOverview as $item)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                        <div class="text-2xl font-extrabold text-slate-950">{{ number_format($item['value']) }}</div>
                        <div class="mt-1 text-sm font-bold text-slate-700">{{ $item['label'] }}</div>
                        <div class="mt-2 text-xs text-slate-400">{{ $item['note'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Category Funnel</h3>
                            <p class="text-xs text-slate-400">Registered to appeared to qualified candidates.</p>
                        </div>
                    </div>
                    <div class="mt-5 space-y-5">
                        @foreach ($categoryFunnel as $item)
                            @php
                                $appearedWidth = round(($item['appeared'] / $maxCategoryRegistered) * 100);
                                $qualifiedWidth = round(($item['qualified'] / $maxCategoryRegistered) * 100);
                            @endphp
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs font-bold text-slate-600">
                                    <span>{{ $item['category'] }}</span>
                                    <span>{{ number_format($item['qualified']) }} qualified</span>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="h-2 rounded-full bg-rose-100">
                                        <div class="h-2 rounded-full bg-rose-500" style="width: {{ round(($item['registered'] / $maxCategoryRegistered) * 100) }}%"></div>
                                    </div>
                                    <div class="h-2 rounded-full bg-blue-100">
                                        <div class="h-2 rounded-full bg-blue-500" style="width: {{ $appearedWidth }}%"></div>
                                    </div>
                                    <div class="h-2 rounded-full bg-emerald-100">
                                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $qualifiedWidth }}%"></div>
                                    </div>
                                </div>
                                <div class="mt-2 grid grid-cols-3 gap-2 text-[10px] font-semibold text-slate-400">
                                    <span>Reg: {{ number_format($item['registered']) }}</span>
                                    <span>App: {{ number_format($item['appeared']) }}</span>
                                    <span>Qual: {{ number_format($item['qualified']) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="text-base font-bold text-slate-900">Year-wise Shift Analysis</h3>
                    <p class="text-xs text-slate-400">2023 to 2025 movement for key NEET indicators.</p>
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs font-bold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="py-2 pr-4">Metric</th>
                                    <th class="py-2 pr-4">2023</th>
                                    <th class="py-2 pr-4">2024</th>
                                    <th class="py-2 pr-4">2025</th>
                                    <th class="py-2 text-right">Shift</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($yearComparison as $item)
                                    <tr>
                                        <td class="py-3 pr-4 font-bold text-slate-800">{{ $item['metric'] }}</td>
                                        <td class="py-3 pr-4 text-slate-600">{{ number_format($item['2023']) }}</td>
                                        <td class="py-3 pr-4 text-slate-600">{{ number_format($item['2024']) }}</td>
                                        <td class="py-3 pr-4 font-bold text-slate-950">{{ number_format($item['2025']) }}</td>
                                        <td class="py-3 text-right">
                                            <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $item['shift'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                {{ $item['shift'] > 0 ? '+' : '' }}{{ number_format($item['shift'], 2) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="text-base font-bold text-slate-900">Top Seat Growth States</h3>
                    <p class="text-xs text-slate-400">2025 increase compared with 2024 total medical seats.</p>
                    <div class="mt-5 space-y-4">
                        @foreach ($seatGrowthStates as $item)
                            <div>
                                <div class="mb-1.5 flex justify-between text-xs font-bold text-slate-600">
                                    <span>{{ $item['state'] }}</span>
                                    <span>+{{ number_format($item['increase']) }} seats</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-rose-500" style="width: {{ max(8, round(($item['increase'] / $maxSeatIncrease) * 100)) }}%"></div>
                                </div>
                                <div class="mt-1 text-[10px] font-semibold text-slate-400">2024: {{ number_format($item['seats_2024']) }} | 2025: {{ number_format($item['seats_2025']) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="text-base font-bold text-slate-900">Medical Colleges: 2024 vs 2025</h3>
                    <p class="text-xs text-slate-400">Institution type comparison from the reference deck.</p>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        @foreach ($collegeTypeComparison as $item)
                            @php $change = $item['2025'] - $item['2024']; @endphp
                            <div class="rounded-xl bg-slate-50 p-4">
                                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $item['type'] }}</div>
                                <div class="mt-3 flex items-end justify-between gap-4">
                                    <div>
                                        <div class="text-2xl font-extrabold text-slate-950">{{ number_format($item['2025']) }}</div>
                                        <div class="text-xs text-slate-400">2025 colleges</div>
                                    </div>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $change > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $change > 0 ? '+' : '' }}{{ number_format($change) }}
                                    </span>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-white">
                                    <div class="h-2 rounded-full bg-slate-800" style="width: {{ max(8, round(($item['2025'] / $maxCollegeTypeCount) * 100)) }}%"></div>
                                </div>
                                <div class="mt-1 text-[10px] font-semibold text-slate-400">2024: {{ number_format($item['2024']) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Analytics Dashboard Row -->
        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            <!-- Left 2 Cols: Dataset Coverage -->
            <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Dataset Coverage</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Distribution of imported datasets by year and course categories.</p>
                    </div>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <!-- By Year -->
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            By Academic Year
                        </h3>
                        <div class="space-y-4">
                            @forelse ($datasetsByYear as $item)
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between text-xs font-semibold text-slate-700">
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                            NEET {{ $item['year'] }}
                                        </span>
                                        <span class="text-slate-900 font-bold bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">{{ number_format($item['count']) }} sheets</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-rose-500 transition-all duration-300" style="width: {{ max(8, round(($item['count'] / $maxYearCount) * 100)) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 py-2">No dataset records available.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- By Course -->
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-800"></span>
                            By Course Category
                        </h3>
                        <div class="space-y-4">
                            @forelse ($datasetsByCourse as $item)
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between text-xs font-semibold text-slate-700">
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M22.25 12v4.25m-1.5-3.612v-2.08L22.25 12" />
                                            </svg>
                                            {{ $item['course'] }}
                                        </span>
                                        <span class="text-slate-900 font-bold bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">{{ number_format($item['count']) }} sheets</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-slate-800 transition-all duration-300" style="width: {{ max(8, round(($item['count'] / $maxCourseCount) * 100)) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 py-2">No course data available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Latest Import -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h2 class="text-lg font-bold text-slate-900">Latest Import</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Details of the most recently processed Excel workbook.</p>
                </div>

                @if ($latestImport)
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                        <div class="flex flex-col gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-850 truncate" title="{{ $latestImport->dataset?->title ?? 'Unknown dataset' }}">
                                    {{ $latestImport->dataset?->title ?? 'Unknown dataset' }}
                                </p>
                                <p class="mt-1.5 text-xs font-semibold text-slate-400 truncate" title="{{ $latestImport->original_filename }}">
                                    {{ $latestImport->original_filename }}
                                </p>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold {{ $latestImport->status === 'completed' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/10' : ($latestImport->status === 'failed' ? 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/10' : 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/10') }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $latestImport->status === 'completed' ? 'bg-emerald-500' : ($latestImport->status === 'failed' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                    {{ ucfirst($latestImport->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-4 border-t border-slate-200/60 pt-4 text-xs">
                            <div>
                                <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Total Rows</p>
                                <p class="mt-1 text-sm font-extrabold text-slate-800">{{ number_format($latestImport->total_rows ?? 0) }}</p>
                            </div>
                            <div>
                                <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Imported Date</p>
                                <p class="mt-1 text-sm font-extrabold text-slate-800">{{ optional($latestImport->created_at)->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <p class="mt-2 text-sm font-semibold text-slate-400">No imports run yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- 3-Column Grid for Lists Section: Datasets, Activity log, & Quick links -->
        <div class="mt-8 grid gap-6 md:grid-cols-3">
            <!-- 1. Top Result Datasets -->
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm flex flex-col justify-between">
                <div>
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-base font-bold text-slate-900">Top Result Datasets</h2>
                        <p class="text-[11px] text-slate-450 mt-0.5">Largest datasets by imported record count.</p>
                    </div>
                    <div class="divide-y divide-slate-100 max-h-[360px] overflow-y-auto">
                        @forelse ($topDatasets as $dataset)
                            <a href="{{ route('results.show', $dataset) }}" class="group block px-5 py-3.5 transition duration-150 hover:bg-slate-50/80">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 group-hover:text-rose-600 transition-colors truncate" title="{{ $dataset->title }}">{{ $dataset->title }}</p>
                                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                            {{ $dataset->course ?? 'NEET' }} • {{ $dataset->year ?? 'Dynamic' }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-600 ring-1 ring-inset ring-rose-500/10">
                                        {{ number_format($dataset->rank_records_count) }}
                                    </span>
                                </div>
                                <div class="mt-2.5 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-rose-500/80 transition-all duration-300 group-hover:bg-rose-500" style="width: {{ max(5, round(($dataset->rank_records_count / $maxDatasetRecords) * 100)) }}%"></div>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-8 text-center text-xs font-semibold text-slate-400">No datasets found.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 2. Recent Imports Log -->
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm flex flex-col justify-between">
                <div>
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-base font-bold text-slate-900">Recent Imports</h2>
                        <p class="text-[11px] text-slate-450 mt-0.5">Operational history of uploaded sheets.</p>
                    </div>
                    <div class="divide-y divide-slate-100 max-h-[360px] overflow-y-auto">
                        @forelse ($recentImports as $import)
                            <div class="px-5 py-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate" title="{{ $import->dataset?->title ?? 'Unknown dataset' }}">{{ $import->dataset?->title ?? 'Unknown dataset' }}</p>
                                        <p class="mt-1 text-[10px] text-slate-400 truncate" title="{{ $import->original_filename }}">{{ $import->original_filename }}</p>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[9px] font-bold {{ $import->status === 'completed' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/10' : ($import->status === 'failed' ? 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/10' : 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/10') }}">
                                        {{ ucfirst($import->status) }}
                                    </span>
                                </div>
                                <div class="mt-2.5 flex items-center justify-between text-[10px] text-slate-400 font-semibold">
                                    <span>{{ number_format($import->total_rows ?? 0) }} rows</span>
                                    <span>{{ optional($import->created_at)->format('d M Y') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-xs font-semibold text-slate-400">No import history yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 3. Recent Result Pages -->
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm flex flex-col justify-between">
                <div>
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-base font-bold text-slate-900">Recent Result Pages</h2>
                        <p class="text-[11px] text-slate-450 mt-0.5">Quick access links to active pages.</p>
                    </div>
                    <div class="divide-y divide-slate-100 max-h-[360px] overflow-y-auto">
                        @forelse ($latestPages as $item)
                            <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="group flex items-center justify-between gap-3 px-5 py-4 transition duration-150 hover:bg-slate-50/80">
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-800 group-hover:text-rose-600 transition-colors truncate">{{ $item['label'] }}</div>
                                    <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $item['group'] }}</div>
                                </div>
                                <span class="shrink-0 inline-flex items-center gap-0.5 text-xs font-bold text-rose-500 group-hover:text-rose-600 transition-colors">
                                    <span>Open</span>
                                    <svg class="h-3 w-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </span>
                            </a>
                        @empty
                            <div class="px-5 py-8 text-center text-xs font-semibold text-slate-400">No pages found.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
