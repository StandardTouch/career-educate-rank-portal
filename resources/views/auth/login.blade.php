<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Portal Access</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Sign in</h1>
            </div>

            <form action="{{ route('login.store') }}" method="POST" class="p-6 space-y-5">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="phone" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Mobile Number</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autofocus
                        placeholder="10 digit mobile number"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <button type="submit" class="w-full rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                    Send Login OTP
                </button>

                <p class="text-center text-sm text-slate-500">
                    New student?
                    <a href="{{ route('register') }}" class="font-bold text-rose-500 hover:text-rose-600">Create an account</a>
                </p>
            </form>
        </section>
    </main>
</body>

</html>
