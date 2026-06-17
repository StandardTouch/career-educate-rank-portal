<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Admin Portal</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Admin sign in</h1>
                <p class="mt-2 text-sm text-slate-500">Use your admin email and password to manage imports, users, and result data.</p>
            </div>

            <form action="{{ route('admin.login.store') }}" method="POST" class="space-y-5 p-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="email" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        placeholder="admin@example.com"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Password</label>
                    <input id="password" name="password" type="password" required
                        placeholder="Enter admin password"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <label class="flex items-center gap-2 text-sm font-medium text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-rose-500 focus:ring-rose-500">
                    Remember this admin session
                </label>

                <button type="submit" class="w-full rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                    Login to Admin
                </button>

                <p class="text-center text-sm text-slate-500">
                    Student login?
                    <a href="{{ route('login') }}" class="font-bold text-rose-500 hover:text-rose-600">Use mobile OTP</a>
                </p>
            </form>
        </section>
    </main>
</body>

</html>
