<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Registration - Career Educate</title>
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
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Verified: {{ $phone }}</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Create your account</h1>
            </div>

            <form action="{{ route('register.details.store') }}" method="POST" class="p-6 space-y-5">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="name" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Username</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label for="neet_rank" class="block text-sm font-bold uppercase tracking-wide text-slate-600">NEET Rank</label>
                    <input id="neet_rank" name="neet_rank" type="number" value="{{ old('neet_rank') }}"
                        placeholder="Optional"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label for="neet_marks" class="block text-sm font-bold uppercase tracking-wide text-slate-600">NEET Marks</label>
                    <input id="neet_marks" name="neet_marks" type="number" step="0.01" value="{{ old('neet_marks') }}"
                        placeholder="Optional"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label for="state" class="block text-sm font-bold uppercase tracking-wide text-slate-600">State</label>
                    <input id="state" name="state" type="text" value="{{ old('state') }}" placeholder="Optional"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label for="quota" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Quota</label>
                    <input id="quota" name="quota" type="text" value="{{ old('quota') }}" placeholder="Optional"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label for="category" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Category</label>
                    <input id="category" name="category" type="text" value="{{ old('category') }}" placeholder="Optional"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <button type="submit" class="w-full rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                    Save Profile and Choose Subscription
                </button>
            </form>
        </section>
    </main>
</body>

</html>
