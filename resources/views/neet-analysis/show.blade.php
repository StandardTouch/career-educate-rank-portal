<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEET UG 2025 Analysis - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
    @include('partials.anti-copy')
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    @php
        $maxGender = max(1, collect($genderDistribution)->max('value'));
        $maxNationality = max(1, collect($nationalityBreakdown)->max('value'));
        $maxCategory = max(1, collect($categoryFunnel)->where('category', '!=', 'Total')->max('registered'));
        $maxSeatsIncrease = max(1, collect($medicalSeats)->where('state', '!=', 'Grand Total')->max('increase'));
        $maxCollegeStates = max(1, collect($topCollegeStates)->max('colleges'));
        $maxCollegeType = max(1, collect($collegeTypes)->max('2025'));
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-slate-950 px-6 py-8 text-white sm:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-rose-300">NEET UG 2025 Analysis</p>
                <h1 class="mt-3 text-4xl font-extrabold tracking-tight sm:text-5xl">NEET UG 2025 - At a Glance</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-300">
                    Registration, infrastructure, gender highlights, category performance, year-wise movement, seats availability, and medical college growth.
                </p>
            </div>

            <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($overview as $item)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                        <div class="text-3xl font-extrabold text-slate-950">{{ number_format($item['value']) }}</div>
                        <div class="mt-1 text-sm font-bold text-slate-700">{{ $item['label'] }}</div>
                        <div class="mt-2 text-xs font-medium text-slate-400">{{ $item['note'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-extrabold text-slate-950">Gender Distribution</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($genderDistribution as $item)
                        <div>
                            <div class="flex justify-between text-sm font-bold text-slate-700">
                                <span>{{ $item['label'] }}</span>
                                <span>{{ number_format($item['value']) }}</span>
                            </div>
                            <div class="mt-2 h-3 rounded-full bg-slate-100">
                                <div class="h-3 rounded-full bg-rose-500" style="width: {{ max(1, round(($item['value'] / $maxGender) * 100)) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-extrabold text-slate-950">Nationality Breakdown</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($nationalityBreakdown as $item)
                        <div>
                            <div class="flex justify-between text-sm font-bold text-slate-700">
                                <span>{{ $item['label'] }}</span>
                                <span>{{ number_format($item['value']) }}</span>
                            </div>
                            <div class="mt-2 h-3 rounded-full bg-slate-100">
                                <div class="h-3 rounded-full bg-slate-800" style="width: {{ max(1, round(($item['value'] / $maxNationality) * 100)) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-xl font-extrabold text-slate-950">NEET 2025 Overview</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Sl. No.</th>
                            <th class="px-5 py-4">Particulars</th>
                            <th class="px-5 py-4 text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($neetOverviewRows as $row)
                            <tr>
                                <td class="px-5 py-4 font-bold text-slate-500">{{ $row['sl'] }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $row['particular'] }}</td>
                                <td class="px-5 py-4 text-right font-extrabold text-slate-950">{{ $row['count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-950">Category-wise Candidate Funnel</h2>
                    <p class="mt-1 text-sm text-slate-500">Registered to Appeared to Qualified - NEET UG 2025.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-600">Registered</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-600">Appeared</span>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-600">Qualified</span>
                </div>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-2">
                @foreach ($categoryFunnel as $item)
                    @continue($item['category'] === 'Total')
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="mb-3 flex justify-between text-sm font-bold text-slate-700">
                            <span>{{ $item['category'] }}</span>
                            <span>{{ number_format($item['qualified']) }} qualified</span>
                        </div>
                        <div class="space-y-2">
                            <div class="h-2.5 rounded-full bg-white"><div class="h-2.5 rounded-full bg-rose-500" style="width: {{ round(($item['registered'] / $maxCategory) * 100) }}%"></div></div>
                            <div class="h-2.5 rounded-full bg-white"><div class="h-2.5 rounded-full bg-blue-500" style="width: {{ round(($item['appeared'] / $maxCategory) * 100) }}%"></div></div>
                            <div class="h-2.5 rounded-full bg-white"><div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ round(($item['qualified'] / $maxCategory) * 100) }}%"></div></div>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-[11px] font-semibold text-slate-500">
                            <span>Reg: {{ number_format($item['registered']) }}</span>
                            <span>App: {{ number_format($item['appeared']) }}</span>
                            <span>Qual: {{ number_format($item['qualified']) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-xl font-extrabold text-slate-950">Category-wise Candidates Analysis 2025</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4 text-right">Registered Candidates</th>
                            <th class="px-5 py-4 text-right">Appeared</th>
                            <th class="px-5 py-4 text-right">Qualified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($categoryFunnel as $row)
                            <tr class="{{ $row['category'] === 'Total' ? 'bg-rose-50/50 font-extrabold' : '' }}">
                                <td class="px-5 py-4 font-bold text-slate-800">{{ $row['category'] }}</td>
                                <td class="px-5 py-4 text-right">{{ number_format($row['registered']) }}</td>
                                <td class="px-5 py-4 text-right">{{ number_format($row['appeared']) }}</td>
                                <td class="px-5 py-4 text-right">{{ number_format($row['qualified']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-xl font-extrabold text-slate-950">YEAR-wise Analysis of NEET</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Sl No</th>
                            <th class="px-5 py-4">Candidates</th>
                            <th class="px-5 py-4">2023</th>
                            <th class="px-5 py-4">2024</th>
                            <th class="px-5 py-4">2025</th>
                            <th class="px-5 py-4 text-right">Shift Analysis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($yearAnalysis as $row)
                            @php $isPositive = str_starts_with($row['shift'], '0') || (! str_starts_with($row['shift'], '-') && $row['shift'] !== '-'); @endphp
                            <tr>
                                <td class="px-5 py-4 font-bold text-slate-500">{{ $row['sl'] }}</td>
                                <td class="px-5 py-4 font-bold text-slate-800">{{ $row['metric'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $row['2023'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $row['2024'] }}</td>
                                <td class="px-5 py-4 font-bold text-slate-950">{{ $row['2025'] }}</td>
                                <td class="px-5 py-4 text-right">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $row['shift'] === '-' ? 'bg-slate-100 text-slate-500' : ($isPositive ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ $row['shift'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-xl font-extrabold text-slate-950">Category-wise Performance of Qualified Candidates NEET (UG) - 2025</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Sl.No.</th>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Qualifying Criteria</th>
                            <th class="px-5 py-4">Marks Range</th>
                            <th class="px-5 py-4 text-right">No. of Candidates Qualified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($qualifiedPerformance as $row)
                            <tr>
                                <td class="px-5 py-4 font-bold text-slate-500">{{ $row['sl'] }}</td>
                                <td class="px-5 py-4 font-bold text-slate-800">{{ $row['category'] }}</td>
                                <td class="px-5 py-4">{{ $row['criteria'] }}</td>
                                <td class="px-5 py-4">{{ $row['marks'] }}</td>
                                <td class="px-5 py-4 text-right font-extrabold text-slate-950">{{ $row['qualified'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-rose-50/50 font-extrabold">
                            <td class="px-5 py-4" colspan="4">Total</td>
                            <td class="px-5 py-4 text-right">12,36,531</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-extrabold text-slate-950">Top States by Number of Colleges</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($topCollegeStates as $item)
                        <div>
                            <div class="mb-1.5 flex justify-between text-sm font-bold text-slate-700">
                                <span>{{ $item['state'] }}</span>
                                <span>{{ number_format($item['colleges']) }}</span>
                            </div>
                            <div class="h-3 rounded-full bg-slate-100">
                                <div class="h-3 rounded-full bg-rose-500" style="width: {{ round(($item['colleges'] / $maxCollegeStates) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-extrabold text-slate-950">Medical Colleges in India 2024 Vs 2025</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($collegeTypes as $item)
                        @php $change = $item['2025'] - $item['2024']; @endphp
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $item['type'] }}</div>
                            <div class="mt-3 flex items-end justify-between">
                                <div>
                                    <div class="text-3xl font-extrabold text-slate-950">{{ number_format($item['2025']) }}</div>
                                    <div class="text-xs text-slate-400">Medical Colleges in 2025</div>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $change > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $change > 0 ? '+' : '' }}{{ number_format($change) }}
                                </span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-white">
                                <div class="h-2 rounded-full bg-slate-800" style="width: {{ max(8, round(($item['2025'] / $maxCollegeType) * 100)) }}%"></div>
                            </div>
                            <div class="mt-1 text-[10px] font-semibold text-slate-400">Medical Colleges in 2024: {{ number_format($item['2024']) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-xl font-extrabold text-slate-950">Medical Seats Availability across India States</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Sl No</th>
                            <th class="px-5 py-4">States</th>
                            <th class="px-5 py-4 text-right">2024 Total Seats</th>
                            <th class="px-5 py-4 text-right">2025 Total Seats</th>
                            <th class="px-5 py-4 text-right">Total Increased Seats</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($medicalSeats as $row)
                            <tr class="{{ $row['state'] === 'Grand Total' ? 'bg-rose-50/50 font-extrabold' : '' }}">
                                <td class="px-5 py-4 font-bold text-slate-500">{{ $row['sl'] }}</td>
                                <td class="px-5 py-4 font-bold text-slate-800">{{ $row['state'] }}</td>
                                <td class="px-5 py-4 text-right">{{ number_format($row['2024']) }}</td>
                                <td class="px-5 py-4 text-right font-bold text-slate-950">{{ number_format($row['2025']) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <span class="{{ $row['increase'] < 0 ? 'text-rose-600' : 'text-emerald-700' }} font-extrabold">
                                        {{ $row['increase'] > 0 ? '+' : '' }}{{ number_format($row['increase']) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>
