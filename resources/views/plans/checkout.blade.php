<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden relative">
            
            <!-- Payment Simulator Overlay Modal -->
            <div id="payment-spinner" class="hidden absolute inset-0 bg-white/95 z-50 flex flex-col items-center justify-center p-6 text-center transition-opacity duration-300">
                <div class="w-16 h-16 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
                <h3 class="mt-6 text-xl font-bold text-slate-900">Simulating Secure Payment</h3>
                <p class="mt-2 text-sm text-slate-500">Contacting gateway. Please do not close or reload this page.</p>
                <div class="mt-6 px-4 py-2 rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                    Razorpay Sandbox Mock Gateway
                </div>
            </div>

            <div class="px-6 py-5 border-b border-slate-200">
                <a href="{{ route('plans.index') }}" class="text-xs font-bold text-rose-500 hover:text-rose-600 tracking-wide">← Change Package</a>
                <h1 class="mt-3 text-2xl font-extrabold text-slate-950">Confirm Purchase</h1>
            </div>

            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Selected Plan</p>
                <h3 class="mt-1 text-base font-bold text-slate-900">{{ $planName }}</h3>
                <div class="mt-3 flex justify-between items-baseline">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Amount Due</span>
                    <span class="text-2xl font-extrabold text-slate-950">₹{{ number_format($price) }}</span>
                </div>
            </div>

            <form id="payment-form" action="{{ route('plans.pay') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="plan" value="{{ $plan }}">
                <input type="hidden" name="amount" value="{{ $price }}">

                <div>
                    <label for="card_name" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Cardholder Name</label>
                    <input id="card_name" name="card_name" type="text" required
                        value="{{ auth()->user()->name }}"
                        placeholder="John Doe"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label for="card_number" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Card Number (Mock)</label>
                    <input id="card_number" name="card_number" type="text" required
                        placeholder="4111 1111 1111 1111"
                        value="4111 1111 1111 1111"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="card_expiry" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Expiry Date</label>
                        <input id="card_expiry" name="card_expiry" type="text" required
                            placeholder="12/28"
                            value="12/28"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label for="card_cvv" class="block text-sm font-bold uppercase tracking-wide text-slate-600">CVV</label>
                        <input id="card_cvv" name="card_cvv" type="password" required
                            placeholder="123"
                            value="123"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                </div>

                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-xs text-slate-500">
                    <p class="font-bold text-slate-700">Developers Sandbox Note:</p>
                    <p class="mt-1">This payment is a simulation. It does not transfer real funds. Clicking "Pay Now" will immediately register a successful mock purchase.</p>
                </div>

                <button type="submit" class="w-full rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                    Pay ₹{{ number_format($price) }} Now
                </button>
            </form>
        </section>
    </main>

    <script>
        document.getElementById('payment-form')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const spinner = document.getElementById('payment-spinner');
            if (spinner) {
                spinner.classList.remove('hidden');
            }
            setTimeout(() => {
                this.submit();
            }, 2500); // 2.5 seconds simulated latency
        });
    </script>
</body>

</html>
