<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Career Educate</title>
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
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ $eyebrow }}</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ $title }}</h1>
                <p class="mt-2 text-slate-500 max-w-2xl">{{ $description }}</p>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Pending File</p>
                    <p class="mt-1 text-sm font-bold text-slate-950">{{ $originalName }}</p>
                    @if (!empty($meta['year']) || !empty($meta['state']) || !empty($meta['descriptor']))
                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            {{ collect([$meta['state'] ?? null, $meta['year'] ?? null, $meta['descriptor'] ?? null])->filter()->implode(' | ') }}
                        </p>
                    @endif
                    @if (!empty($meta['sheet_names']))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (array_slice($meta['sheet_names'], 0, 8) as $sheetName)
                                <span class="rounded-full bg-white px-2 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">{{ $sheetName }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <form action="{{ $action }}" method="POST" class="mt-6 space-y-6">
                    @csrf

                    <input type="hidden" name="suggested_alias" value="{{ $suggestedCourse ?: '' }}">

                    <div>
                        <label for="course" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Course Tab</label>
                        <input
                            id="course"
                            name="course"
                            list="course-options"
                            value="{{ old('course', $suggestedCourse ?: '') }}"
                            placeholder="Example: BHMS"
                            required
                            class="mt-3 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                        >
                        <datalist id="course-options">
                            @foreach ($courses as $course)
                                <option value="{{ $course }}"></option>
                            @endforeach
                        </datalist>
                        <p class="mt-2 text-xs text-slate-500">
                            Type a new tab name like BHMS, or choose an existing one like MBBS/BDS to keep this file under that tab.
                        </p>
                    </div>

                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <input type="checkbox" name="remember_mapping" value="1" checked class="mt-1 h-4 w-4 rounded border-slate-300 text-rose-500 focus:ring-rose-500">
                        <span>
                            <span class="block text-sm font-bold text-slate-800">Remember this choice for future imports</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">
                                Future filenames containing this detected course text will automatically use the selected tab.
                            </span>
                        </span>
                    </label>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-800">
                        If this file matches an existing dataset slug after the course is selected, that dataset's rows will be replaced the same way current imports work.
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <button type="submit" class="inline-flex justify-center rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                            Continue Import
                        </button>
                        <a href="{{ $cancelRoute }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>

</html>
