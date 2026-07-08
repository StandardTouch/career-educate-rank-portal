<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Call Lookup - Career Educate Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
    @include('partials.anti-copy')
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Call Details</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">Student Call Lookup</h1>
                <p class="mt-1 text-sm text-slate-500">View student call history from Exotel by saved phone number.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.career-educate-call-log') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-rose-600">
                    Career Educate Call Log
                </a>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-rose-300 hover:text-rose-600">
                    Back to Dashboard
                </a>
            </div>
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
                            <tr class="align-top">
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
                                        <a href="{{ route('admin.call-history', $student) }}"
                                            target="_blank"
                                            rel="noopener"
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
    </main>
</body>

</html>
