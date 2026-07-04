<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imports - Career Educate</title>
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
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">All Imports</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review uploaded result and predicted rank files. Deleting an import removes its imported page, records, rounds, sheets, stored upload, and navigation entry.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('import.excel') }}" class="inline-flex justify-center rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-600">
                    Import Results
                </a>
                <a href="{{ route('import.analysis') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                    Import Predicted Rank
                </a>
            </div>
        </section>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.imports') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1">
                    <label for="search" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Search Imports</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search by file, dataset, course, status, year, state, quota, or YYYY-MM-DD"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                    >
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="inline-flex justify-center rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-600">
                        Search
                    </button>
                    @if ($search !== '')
                        <a href="{{ route('admin.imports') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-950">Result Imports</h2>
                <p class="mt-1 text-xs text-slate-500">Files imported through the result sheet import flow. Showing {{ number_format($resultImports->total()) }} result imports.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Dataset</th>
                            <th class="px-6 py-3">File</th>
                            <th class="px-6 py-3">Course</th>
                            <th class="px-6 py-3">Rows</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Imported</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($resultImports as $import)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $import->dataset?->title ?? 'Deleted dataset' }}</div>
                                    @if ($import->dataset)
                                        <a href="{{ route('results.show', $import->dataset) }}" class="mt-1 inline-flex text-xs font-bold text-rose-500 hover:text-rose-600">Open page</a>
                                    @endif
                                </td>
                                <td class="max-w-xs px-6 py-4">
                                    <div class="truncate font-semibold text-slate-700" title="{{ $import->original_filename }}">{{ $import->original_filename }}</div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-600">{{ $import->dataset?->course ?? '-' }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-600">{{ number_format($import->total_rows ?? 0) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $import->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($import->status === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                        {{ ucfirst($import->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-500">{{ optional($import->created_at)->format('d M Y, h:i A') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.imports.results.destroy', $import) }}" method="POST" onsubmit="return confirm('Delete this result import and remove its imported page from everywhere?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm font-semibold text-slate-400">No result imports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($resultImports->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $resultImports->links() }}
                </div>
            @endif
        </section>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-950">Predicted Rank Imports</h2>
                <p class="mt-1 text-xs text-slate-500">Files imported through the predicted rank import flow. Showing {{ number_format($predictedRankImports->total()) }} predicted rank imports.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Dataset</th>
                            <th class="px-6 py-3">File</th>
                            <th class="px-6 py-3">Course</th>
                            <th class="px-6 py-3">Rows</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Imported</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($predictedRankImports as $import)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $import->analysisDataset?->title ?? 'Deleted dataset' }}</div>
                                    @if ($import->analysisDataset)
                                        <a href="{{ route('analysis.show', $import->analysisDataset) }}" class="mt-1 inline-flex text-xs font-bold text-rose-500 hover:text-rose-600">Open page</a>
                                    @endif
                                </td>
                                <td class="max-w-xs px-6 py-4">
                                    <div class="truncate font-semibold text-slate-700" title="{{ $import->original_filename }}">{{ $import->original_filename }}</div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-600">{{ $import->analysisDataset?->course ?? '-' }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-600">{{ number_format($import->total_rows ?? 0) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $import->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($import->status === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                        {{ ucfirst($import->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-500">{{ optional($import->created_at)->format('d M Y, h:i A') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.imports.predicted-rank.destroy', $import) }}" method="POST" onsubmit="return confirm('Delete this predicted rank import and remove its imported page from everywhere?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm font-semibold text-slate-400">No predicted rank imports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($predictedRankImports->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $predictedRankImports->links() }}
                </div>
            @endif
        </section>
    </main>
</body>

</html>
