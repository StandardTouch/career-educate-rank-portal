<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duplicate Comparison - Career Educate</title>
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
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ $comparison['label'] }}</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">Duplicate Comparison</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Compare the two selected imports side by side, including metadata, sheet counts, and available imported row samples.
                </p>
            </div>
            <a href="{{ route('admin.imports.duplicates') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                Back To Duplicates
            </a>
        </section>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid lg:grid-cols-2">
                @foreach (['first' => $comparison['first'], 'second' => $comparison['second']] as $side => $item)
                    <div class="border-slate-100 p-6 first:border-b lg:first:border-b-0 lg:first:border-r">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ ucfirst($side) }} Import</p>
                        <h2 class="mt-1 break-words text-xl font-extrabold text-slate-950">{{ $item['dataset_title'] }}</h2>
                        <p class="mt-2 break-words text-sm font-semibold text-slate-600">{{ $item['original_filename'] }}</p>

                        <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Status</dt>
                                <dd class="mt-1 font-bold text-slate-900">{{ ucfirst($item['status']) }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Rows</dt>
                                <dd class="mt-1 font-bold text-slate-900">{{ number_format($item['total_rows'] ?? 0) }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Course</dt>
                                <dd class="mt-1 font-bold text-slate-900">{{ $item['course'] ?? '-' }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Year</dt>
                                <dd class="mt-1 font-bold text-slate-900">{{ $item['year'] ?? '-' }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">State</dt>
                                <dd class="mt-1 font-bold text-slate-900">{{ $item['state'] ?? '-' }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Imported</dt>
                                <dd class="mt-1 font-bold text-slate-900">{{ $item['created_at']?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</dd>
                            </div>
                        </dl>

                        @if ($item['page_url'])
                            <a href="{{ $item['page_url'] }}" target="_blank" rel="noopener" class="mt-5 inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                                Open Page
                            </a>
                        @endif
                        <form action="{{ $item['delete_url'] }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this duplicate import entry? The imported page will remain available through the remaining entry.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 transition hover:bg-rose-100">
                                Delete Duplicate Entry
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-950">Sheet Comparison</h2>
                <p class="mt-1 text-xs text-slate-500">Matching sheet names are compared by row count.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Sheet</th>
                            <th class="px-6 py-3">First Rows</th>
                            <th class="px-6 py-3">Second Rows</th>
                            <th class="px-6 py-3">Match</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($comparison['sheet_comparison'] as $sheet)
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $sheet['name'] }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-600">{{ $sheet['first_rows'] === null ? '-' : number_format($sheet['first_rows']) }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-600">{{ $sheet['second_rows'] === null ? '-' : number_format($sheet['second_rows']) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $sheet['matches'] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $sheet['matches'] ? 'Same rows' : 'Different or missing' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm font-semibold text-slate-400">No sheet data was stored for these imports.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-950">Available Row Samples</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Older imports may not have row samples because current re-imports replace dataset rows.
                </p>
            </div>

            <div class="mt-5 grid gap-6 lg:grid-cols-2">
                @foreach (['first' => $comparison['row_samples']['first'], 'second' => $comparison['row_samples']['second']] as $side => $rows)
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">{{ ucfirst($side) }} Import Rows</h3>
                        <div class="mt-3 grid gap-2">
                            @forelse ($rows as $row)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                                    <div><span class="font-bold text-slate-900">College:</span> {{ $row->college_name ?? '-' }}</div>
                                    <div><span class="font-bold text-slate-900">Course:</span> {{ $row->course ?? '-' }}</div>
                                    <div><span class="font-bold text-slate-900">Category:</span> {{ $row->category ?? '-' }}</div>
                                    <div><span class="font-bold text-slate-900">Closing Rank:</span> {{ $row->closing_rank ?? '-' }}</div>
                                    <div><span class="font-bold text-slate-900">Marks:</span> {{ $row->marks ?? '-' }}</div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5 text-sm font-semibold text-slate-400">
                                    No row sample is available for this import.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </main>
</body>

</html>
