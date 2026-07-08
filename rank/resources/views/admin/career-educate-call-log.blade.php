<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Educate Call Log - Career Educate Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
    @include('partials.anti-copy')
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    @php
        $totalCalls = (int) ($metadata['Total'] ?? count($calls));
        $incomingTotal = (int) ($metadata['IncomingTotal'] ?? 0);
        $outgoingTotal = (int) ($metadata['OutgoingTotal'] ?? 0);
        $pageSize = max(1, (int) ($metadata['PageSize'] ?? 100));
        $currentCallPage = max(0, (int) ($metadata['Page'] ?? $callPage));
        $hasPreviousCalls = $currentCallPage > 0;
        $hasNextCalls = !empty($metadata['NextPageUri'] ?? null) || (($currentCallPage + 1) * $pageSize < $totalCalls);
        $firstCallNumber = (int) ($metadata['Start'] ?? ($totalCalls > 0 ? ($currentCallPage * $pageSize) + 1 : 0));
        $lastCallNumber = (int) ($metadata['End'] ?? min(($currentCallPage + 1) * $pageSize, $totalCalls));
        $totalCallPages = max(1, (int) ceil(max(1, $totalCalls) / $pageSize));
        $pageWindowStart = max(0, $currentCallPage - 2);
        $pageWindowEnd = min($totalCallPages - 1, $currentCallPage + 2);
        $visibleCallPages = range($pageWindowStart, $pageWindowEnd);
        $filterQuery = collect($filters ?? [])
            ->filter(fn ($value) => filled($value))
            ->merge(['page_size' => $pageSize])
            ->all();

        $statusClasses = [
            'completed' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/10',
            'busy' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/10',
            'failed' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/10',
            'no-answer' => 'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/10',
            'queued' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/10',
            'in-progress' => 'bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-600/10',
        ];
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Call Details</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">Career Educate Call Log</h1>
                <p class="mt-1 text-sm text-slate-500">All Exotel calls linked to Career Educate exophone {{ $exophone }} with live call status.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.call-details') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-rose-300 hover:text-rose-600">
                    Student Call Lookup
                </a>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-rose-300 hover:text-rose-600">
                    Dashboard
                </a>
            </div>
        </section>

        <section class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Exophone</p>
                <div class="mt-2 text-2xl font-extrabold text-slate-950">{{ $exophone }}</div>
                <p class="mt-1 text-xs font-semibold text-slate-400">Assigned app: Career Educate</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Call Records</p>
                <div class="mt-2 text-2xl font-extrabold text-slate-950">{{ number_format($totalCalls) }}</div>
                <p class="mt-1 text-xs font-semibold text-slate-400">Showing page {{ $currentCallPage + 1 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Direction Split</p>
                <div class="mt-2 flex items-baseline gap-4">
                    <span class="text-2xl font-extrabold text-slate-950">{{ number_format($incomingTotal) }}</span>
                    <span class="text-sm font-bold text-slate-400">in</span>
                    <span class="text-2xl font-extrabold text-slate-950">{{ number_format($outgoingTotal) }}</span>
                    <span class="text-sm font-bold text-slate-400">out</span>
                </div>
                <p class="mt-1 text-xs font-semibold text-slate-400">Based on Exotel direction values.</p>
            </div>
        </section>

        <form method="GET" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">From Date</span>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">To Date</span>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Phone</span>
                    <input type="search" name="phone" value="{{ $filters['phone'] ?? '' }}" placeholder="From or To..."
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Status</span>
                    <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        <option value="">All statuses</option>
                        @foreach (['completed' => 'Completed', 'busy' => 'Busy', 'failed' => 'Failed', 'no-answer' => 'No answer', 'canceled' => 'Canceled', 'in-progress' => 'In progress', 'queued' => 'Queued'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Direction</span>
                    <select name="direction" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        <option value="">All directions</option>
                        <option value="in" @selected(($filters['direction'] ?? '') === 'in')>Incoming</option>
                        <option value="out" @selected(($filters['direction'] ?? '') === 'out')>Outgoing</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Rows</span>
                    <select name="page_size" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button class="rounded-xl bg-rose-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-rose-600">
                    Apply Filters
                </button>
                <a href="{{ route('admin.career-educate-call-log') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:border-rose-300 hover:text-rose-600">
                    Reset
                </a>
            </div>
        </form>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Career Educate Calls</h2>
                    <p class="mt-1 text-xs text-slate-400">Call history and statuses for the configured Career Educate exophone.</p>
                </div>
                @if (!$apiError)
                    <div class="text-xs font-bold text-slate-500">
                        Page {{ $currentCallPage + 1 }} of {{ $totalCallPages }} | {{ number_format($totalCalls) }} total
                    </div>
                @endif
            </div>

            @if ($apiError)
                <div class="m-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                    {{ $apiError }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Call</th>
                                <th class="px-5 py-4">From / To</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Timing</th>
                                <th class="px-5 py-4">Recording</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($calls as $call)
                                @php
                                    $status = strtolower((string) ($call['Status'] ?? 'unknown'));
                                    $recordingUrl = $call['RecordingUrl'] ?? $call['RecordingURL'] ?? $call['recording_url'] ?? null;
                                    $recordingProxyUrl = $recordingUrl ? route('admin.call-recording', ['url' => $recordingUrl]) : null;
                                @endphp
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-950">{{ $call['Sid'] ?? $call['CallSid'] ?? 'Call' }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $call['Direction'] ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        <div><span class="font-bold text-slate-700">From:</span> {{ $call['From'] ?? '-' }}</div>
                                        <div class="mt-1"><span class="font-bold text-slate-700">To:</span> {{ $call['To'] ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ str_replace('-', ' ', ucfirst($status)) }}
                                        </span>
                                        <div class="mt-2 text-xs text-slate-500">Duration: {{ $call['Duration'] ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-xs text-slate-500">
                                        <div><span class="font-bold text-slate-700">Start:</span> {{ $call['StartTime'] ?? $call['DateCreated'] ?? '-' }}</div>
                                        <div class="mt-1"><span class="font-bold text-slate-700">End:</span> {{ $call['EndTime'] ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($recordingUrl)
                                            <audio controls preload="none" class="w-64 max-w-full">
                                                <source src="{{ $recordingProxyUrl }}">
                                                Your browser does not support the audio element.
                                            </audio>
                                            <div class="mt-2">
                                                <a href="{{ $recordingProxyUrl }}" target="_blank" rel="noopener" class="text-xs font-bold text-rose-500 hover:text-rose-600">Open recording</a>
                                            </div>
                                        @else
                                            <span class="text-xs font-semibold text-slate-400">No recording</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm font-semibold text-slate-400">No calls found for the Career Educate exophone.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 md:flex-row md:items-center md:justify-between">
                    <div class="text-xs font-semibold text-slate-400">
                        Showing {{ number_format($firstCallNumber) }}-{{ number_format($lastCallNumber) }} of {{ number_format($totalCalls) }} calls.
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($hasPreviousCalls)
                            <a href="{{ route('admin.career-educate-call-log', array_merge($filterQuery, ['call_page' => $currentCallPage - 1])) }}"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:border-rose-300 hover:text-rose-600">
                                Previous
                            </a>
                        @endif

                        @if ($pageWindowStart > 0)
                            <a href="{{ route('admin.career-educate-call-log', array_merge($filterQuery, ['call_page' => 0])) }}"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:border-rose-300 hover:text-rose-600">
                                1
                            </a>
                            <span class="px-1 text-xs font-bold text-slate-400">...</span>
                        @endif

                        @foreach ($visibleCallPages as $pageNumber)
                            <a href="{{ route('admin.career-educate-call-log', array_merge($filterQuery, ['call_page' => $pageNumber])) }}"
                                class="rounded-xl px-3 py-2 text-xs font-bold {{ $pageNumber === $currentCallPage ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:border-rose-300 hover:text-rose-600' }}">
                                {{ $pageNumber + 1 }}
                            </a>
                        @endforeach

                        @if ($pageWindowEnd < $totalCallPages - 1)
                            <span class="px-1 text-xs font-bold text-slate-400">...</span>
                            <a href="{{ route('admin.career-educate-call-log', array_merge($filterQuery, ['call_page' => $totalCallPages - 1])) }}"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:border-rose-300 hover:text-rose-600">
                                {{ $totalCallPages }}
                            </a>
                        @endif

                        @if ($hasNextCalls)
                            <a href="{{ route('admin.career-educate-call-log', array_merge($filterQuery, ['call_page' => $currentCallPage + 1])) }}"
                                class="rounded-xl bg-rose-500 px-4 py-2 text-xs font-bold text-white hover:bg-rose-600">
                                Next
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </main>
</body>

</html>
