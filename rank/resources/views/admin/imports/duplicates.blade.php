<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duplicate Imports - Career Educate</title>
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

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 mt-12">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Import Management</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">Duplicate Entries</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review result and predicted rank imports that point to the same imported dataset identity.
                </p>
            </div>
            <a href="{{ route('admin.imports') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                Manage All Imports
            </a>
        </section>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @php
            $sections = [
                ['title' => 'Result Duplicate Entries', 'groups' => $resultGroups],
                ['title' => 'Predicted Rank Duplicate Entries', 'groups' => $analysisGroups],
            ];
        @endphp

        @foreach ($sections as $section)
            <section class="mt-8">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">{{ $section['title'] }}</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ number_format($section['groups']->count()) }} duplicate group{{ $section['groups']->count() === 1 ? '' : 's' }} found.</p>
                    </div>
                </div>

                <div class="grid gap-5">
                    @forelse ($section['groups'] as $group)
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-5 py-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $group['label'] }} | {{ number_format($group['count']) }} matches</p>
                                        <h3 class="mt-1 text-xl font-extrabold text-slate-950">{{ $group['title'] }}</h3>
                                    </div>
                                    <a href="{{ $group['detail_url'] }}" class="inline-flex justify-center rounded-xl bg-rose-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-rose-600">
                                        View Details
                                    </a>
                                </div>
                            </div>

                            <div class="grid gap-0 lg:grid-cols-2">
                                @foreach ([$group['first'], $group['second']] as $item)
                                    <div class="border-slate-100 p-5 first:border-b lg:first:border-b-0 lg:first:border-r">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Imported File</p>
                                                <p class="mt-1 break-words text-sm font-bold text-slate-900">{{ $item['original_filename'] }}</p>
                                            </div>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $item['status'] === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($item['status'] === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                                {{ ucfirst($item['status']) }}
                                            </span>
                                        </div>

                                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                            <div>
                                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Dataset</dt>
                                                <dd class="mt-1 font-semibold text-slate-800">{{ $item['dataset_title'] }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Rows</dt>
                                                <dd class="mt-1 font-semibold text-slate-800">{{ number_format($item['total_rows'] ?? 0) }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Course / Year</dt>
                                                <dd class="mt-1 font-semibold text-slate-800">{{ $item['course'] ?? '-' }} | {{ $item['year'] ?? '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Imported</dt>
                                                <dd class="mt-1 font-semibold text-slate-800">{{ $item['created_at']?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</dd>
                                            </div>
                                        </dl>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @if ($item['page_url'])
                                                <a href="{{ $item['page_url'] }}" target="_blank" rel="noopener" class="inline-flex rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                                                    Open Page
                                                </a>
                                            @endif
                                            <form action="{{ $item['delete_url'] }}" method="POST" onsubmit="return confirm('Delete this duplicate import entry? The imported page will remain available through the remaining entry.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
                            <p class="text-sm font-bold text-slate-400">No duplicate entries found in this section.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </main>
</body>

</html>
