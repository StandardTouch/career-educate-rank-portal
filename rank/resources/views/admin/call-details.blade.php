<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Details - Career Educate Admin</title>
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
        $pageSize = max(1, (int) ($metadata['PageSize'] ?? 100));
        $currentCallPage = max(0, (int) ($metadata['Page'] ?? $callPage));
        $hasPreviousCalls = $currentCallPage > 0;
        $hasNextCalls = !empty($metadata['NextPageUri'] ?? null) || (($currentCallPage + 1) * $pageSize < $totalCalls);
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Admin Calls</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">Call Details</h1>
                <p class="mt-1 text-sm text-slate-500">View student call history from Exotel by saved phone number.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-rose-300 hover:text-rose-600">
                Back to Dashboard
            </a>
        </section>

        <form method="GET" class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_auto]">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search student name, email, phone..."
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            <button class="rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white hover:bg-rose-600">Filter</button>
        </form>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-base font-bold text-slate-900">Students</h2>
                <p class="mt-1 text-xs text-slate-400">Only normal users are listed here.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Student</th>
                            <th class="px-5 py-4">Phone</th>
                            <th class="px-5 py-4">Plan</th>
                            <th class="px-5 py-4">Payment</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($students as $student)
                            <tr class="align-top {{ (int) $selectedUserId === (int) $student->id ? 'bg-rose-50/40' : '' }}">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-950">{{ $student->name }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $student->email }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">{{ $student->phone ?? '-' }}</div>
                                    <div class="mt-1 text-xs {{ $student->mobile_verified_at ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $student->mobile_verified_at ? 'Verified' : 'Not verified' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-semibold capitalize text-slate-700">{{ $student->plan ?? 'none' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $student->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ ucfirst($student->payment_status ?? 'unpaid') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if ($student->phone)
                                        <a href="{{ route('admin.call-details', ['search' => $search, 'user' => $student->id]) }}"
                                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-rose-600">
                                            View Calls
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-400">No Phone</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm font-semibold text-slate-400">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $students->links() }}
            </div>
        </section>

        @if ($selectedUser)
            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Calls for {{ $selectedUser->name }}</h2>
                        <p class="mt-1 text-xs text-slate-400">Phone: {{ $selectedUser->phone ?? '-' }}</p>
                    </div>
                    @if (!$apiError)
                        <div class="text-xs font-bold text-slate-500">
                            Page {{ $currentCallPage + 1 }} | {{ number_format($totalCalls) }} total
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
                                        $recordingUrl = $call['RecordingUrl'] ?? $call['RecordingURL'] ?? $call['recording_url'] ?? null;
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
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                                {{ ucfirst($call['Status'] ?? 'unknown') }}
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
                                                    <source src="{{ $recordingUrl }}">
                                                    Your browser does not support the audio element.
                                                </audio>
                                                <a href="{{ $recordingUrl }}" target="_blank" rel="noopener" class="mt-2 block text-xs font-bold text-rose-500 hover:text-rose-600">Open recording</a>
                                            @else
                                                <span class="text-xs font-semibold text-slate-400">No recording</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-sm font-semibold text-slate-400">No calls found for this phone number.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 md:flex-row md:items-center md:justify-between">
                        <div class="text-xs font-semibold text-slate-400">
                            Showing up to {{ number_format($pageSize) }} calls per API page.
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($hasPreviousCalls)
                                <a href="{{ route('admin.call-details', ['search' => $search, 'user' => $selectedUser->id, 'call_page' => $currentCallPage - 1]) }}"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:border-rose-300 hover:text-rose-600">
                                    Previous
                                </a>
                            @endif
                            @if ($hasNextCalls)
                                <a href="{{ route('admin.call-details', ['search' => $search, 'user' => $selectedUser->id, 'call_page' => $currentCallPage + 1]) }}"
                                    class="rounded-xl bg-rose-500 px-4 py-2 text-xs font-bold text-white hover:bg-rose-600">
                                    Next
                                </a>
                            @endif
                        </div>
                    </div>

                    @if ($rawResponse)
                        <details class="border-t border-slate-100 px-5 py-4">
                            <summary class="cursor-pointer text-sm font-bold text-slate-700">Raw Exotel response</summary>
                            <pre class="mt-3 max-h-96 overflow-auto rounded-xl bg-slate-950 p-4 text-xs text-slate-100">{{ json_encode($rawResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    @endif
                @endif
            </section>
        @endif
    </main>
</body>

</html>
