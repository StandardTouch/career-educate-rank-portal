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
    @include('partials.anti-copy')
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

            <form id="payment-form" action="{{ route('plans.verify') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="plan"                  value="{{ $plan }}">
                <input type="hidden" name="amount"                value="{{ $price }}">
                <input type="hidden" name="razorpay_payment_id"   id="razorpay_payment_id">
                <input type="hidden" name="razorpay_order_id"     id="razorpay_order_id"   value="{{ $orderId }}">
                <input type="hidden" name="razorpay_signature"    id="razorpay_signature">

                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-xs text-slate-500">
                    <p class="font-bold text-slate-700">Secure Checkout</p>
                    <p class="mt-1">By clicking "Pay Now", you will be redirected to our secure payment gateway to complete your purchase.</p>
                </div>

                <button id="rzp-button" type="button" class="w-full rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                    Pay ₹{{ number_format($price) }} Now
                </button>
            </form>
        </section>
    </main>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const options = {
            key:         "{{ config('services.razorpay.key') }}",
            amount:      {{ $price * 100 }},
            currency:    "INR",
            name:        "Career Educate",
            description: "{{ $planName }}",
            order_id:    "{{ $orderId }}",
            handler: function (response) {
                document.getElementById('razorpay_payment_id').value  = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value    = response.razorpay_order_id;
                document.getElementById('razorpay_signature').value   = response.razorpay_signature;
                document.getElementById('payment-form').submit();
            },
            prefill: {
                name:  "{{ auth()->user()->name }}",
                email: "{{ auth()->user()->email }}"
            },
            theme: { color: "#f43f5e" }
        };
        const rzp = new Razorpay(options);
        document.getElementById('rzp-button').onclick = function (e) {
            e.preventDefault();
            rzp.open();
        };
    </script>

</body>

</html>
