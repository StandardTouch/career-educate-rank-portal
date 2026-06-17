<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Career Educate Admin</title>
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
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Admin Payments</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">Transactions</h1>
                <p class="mt-1 text-sm text-slate-500">Track Razorpay orders, transaction ids, plans, and payment status.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                    Revenue: Rs. {{ number_format((float) $totalRevenue, 2) }}
                </div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-rose-300 hover:text-rose-600">
                    Back to Dashboard
                </a>
            </div>
        </section>

        <form method="GET" class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_200px_auto]">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search user, transaction, order..."
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:outline-none">
                <option value="">All status</option>
                @foreach (['pending', 'completed', 'captured', 'failed'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white hover:bg-rose-600">Filter</button>
        </form>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">User</th>
                            <th class="px-5 py-4">Plan</th>
                            <th class="px-5 py-4">Amount</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Transaction ID</th>
                            <th class="px-5 py-4">Order ID</th>
                            <th class="px-5 py-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payments as $payment)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-950">{{ $payment->user?->name ?? 'Deleted user' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $payment->user?->email ?? '-' }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $payment->user?->phone ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 font-semibold capitalize text-slate-700">{{ $payment->plan }}</td>
                                <td class="px-5 py-4 font-bold text-slate-950">Rs. {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ in_array($payment->status, ['completed', 'captured'], true) ? 'bg-emerald-50 text-emerald-700' : ($payment->status === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold text-slate-600">{{ $payment->transaction_id ?? '-' }}</td>
                                <td class="px-5 py-4 text-xs font-semibold text-slate-600">{{ $payment->order_id ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ optional($payment->created_at)->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm font-semibold text-slate-400">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $payments->links() }}
            </div>
        </section>
    </main>
</body>

</html>
