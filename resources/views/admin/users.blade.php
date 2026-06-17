<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Career Educate Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Admin Users</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">User Management</h1>
                <p class="mt-1 text-sm text-slate-500">View students, admins, mobile verification, and subscription status.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-rose-300 hover:text-rose-600">
                Back to Dashboard
            </a>
        </section>

        <form method="GET" class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_180px_auto]">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search name, email, phone..."
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:outline-none">
                <option value="">All payment</option>
                <option value="paid" @selected($status === 'paid')>Paid</option>
                <option value="unpaid" @selected($status === 'unpaid')>Unpaid</option>
            </select>
            <select name="plan" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:outline-none">
                <option value="">All plans</option>
                <option value="none" @selected($plan === 'none')>No plan</option>
                <option value="basic" @selected($plan === 'basic')>Basic</option>
                <option value="premium" @selected($plan === 'premium')>Premium</option>
            </select>
            <button class="rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white hover:bg-rose-600">Filter</button>
        </form>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">User</th>
                            <th class="px-5 py-4">Phone</th>
                            <th class="px-5 py-4">Role</th>
                            <th class="px-5 py-4">Plan</th>
                            <th class="px-5 py-4">Payment</th>
                            <th class="px-5 py-4">NEET</th>
                            <th class="px-5 py-4">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-950">{{ $user->name }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">{{ $user->phone ?? '-' }}</div>
                                    <div class="mt-1 text-xs {{ $user->mobile_verified_at ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $user->mobile_verified_at ? 'Verified' : 'Not verified' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->is_admin ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $user->is_admin ? 'Admin' : 'Student' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-semibold capitalize text-slate-700">{{ $user->plan ?? 'none' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ ucfirst($user->payment_status ?? 'unpaid') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div>Rank: {{ $user->neet_rank ? number_format($user->neet_rank) : '-' }}</div>
                                    <div class="text-xs">Marks: {{ $user->neet_marks !== null ? rtrim(rtrim((string) $user->neet_marks, '0'), '.') : '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ optional($user->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm font-semibold text-slate-400">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $users->links() }}
            </div>
        </section>
    </main>
</body>

</html>
