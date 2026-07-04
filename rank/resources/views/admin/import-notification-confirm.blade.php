<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Notification Title - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
    @include('partials.anti-copy')
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 mt-12">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 pt-8 pb-5 border-b border-slate-200">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Notification Title</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Confirm Notification Title</h1>
                <p class="mt-2 text-slate-500 max-w-2xl">
                    We suggested a title from the PDF filename. Keep it as-is or edit it, then choose which header dropdown should show this PDF.
                </p>
            </div>

            <form action="{{ route('notifications.import.confirm.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Uploaded PDF</p>
                    <p class="mt-1 text-sm font-bold text-slate-950">{{ $originalName }}</p>
                </div>

                <div>
                    <label for="title" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Notification Title</label>
                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $suggestedTitle) }}"
                        required
                        maxlength="160"
                        class="mt-3 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                    >
                </div>

                <div>
                    <label for="dropdown_name" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Header Dropdown</label>
                    <input
                        id="dropdown_name"
                        name="dropdown_name"
                        list="dropdown-options"
                        type="text"
                        value="{{ old('dropdown_name', 'Notifications') }}"
                        required
                        maxlength="80"
                        class="mt-3 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                    >
                    <datalist id="dropdown-options">
                        @foreach ($dropdownOptions as $dropdownOption)
                            <option value="{{ $dropdownOption }}"></option>
                        @endforeach
                    </datalist>
                    <p class="mt-2 text-xs text-slate-500">
                        Choose an existing dropdown or type a new dropdown name.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <button type="submit" class="inline-flex justify-center rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                        Publish PDF
                    </button>
                    <a href="{{ route('notifications.import') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                        Upload Another PDF
                    </a>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
