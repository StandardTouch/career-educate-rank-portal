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
        dialog[open] {
            position: fixed;
            inset: 0;
            margin: auto;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }
        dialog::backdrop { background: rgb(15 23 42 / 0.45); }
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
                    Review uploaded result, predicted rank, and notification files. Deleting an item removes its imported page or dropdown entry from the portal.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('import.excel') }}" class="inline-flex justify-center rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-600">
                    Import Results
                </a>
                <a href="{{ route('import.analysis') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                    Import Predicted Rank
                </a>
                <a href="{{ route('notifications.import') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                    Import PDF
                </a>
                <a href="{{ route('admin.imports.duplicates') }}" class="inline-flex justify-center rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-bold text-amber-800 transition hover:border-amber-300 hover:bg-amber-100">
                    Check Duplicates
                </a>
            </div>
        </section>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                {{ $errors->first() }}
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
                        placeholder="Search by file, title, dropdown, dataset, course, status, year, state, quota, or YYYY-MM-DD"
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
                                    <div class="max-w-56 truncate font-bold text-slate-900" title="{{ $import->dataset?->title ?? 'Deleted dataset' }}">
                                        {{ $import->dataset?->title ?? 'Deleted dataset' }}
                                    </div>
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
                                <td class="px-6 py-4 font-semibold text-slate-500">{{ $import->created_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex flex-col gap-2">
                                        @if ($import->dataset)
                                            <button type="button" data-modal-target="result-edit-{{ $import->id }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                                                Edit
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.imports.results.destroy', $import) }}" method="POST" onsubmit="return confirm('Delete this result import and remove its imported page from everywhere?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
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
                                    <div class="max-w-56 truncate font-bold text-slate-900" title="{{ $import->analysisDataset?->title ?? 'Deleted dataset' }}">
                                        {{ $import->analysisDataset?->title ?? 'Deleted dataset' }}
                                    </div>
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
                                <td class="px-6 py-4 font-semibold text-slate-500">{{ $import->created_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex flex-col gap-2">
                                        @if ($import->analysisDataset)
                                            <button type="button" data-modal-target="analysis-edit-{{ $import->id }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                                                Edit
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.imports.predicted-rank.destroy', $import) }}" method="POST" onsubmit="return confirm('Delete this predicted rank import and remove its imported page from everywhere?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
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

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-950">PDF Imports</h2>
                <p class="mt-1 text-xs text-slate-500">PDFs shown in header dropdowns. Showing {{ number_format($notificationDocuments->total()) }} PDF imports.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Dropdown</th>
                            <th class="px-6 py-3">File</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Uploaded</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($notificationDocuments as $document)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="max-w-64 truncate font-bold text-slate-900" title="{{ $document->title }}">{{ $document->title }}</div>
                                    <a href="{{ route('notifications.view', $document) }}" target="_blank" rel="noopener" class="mt-1 inline-flex text-xs font-bold text-rose-500 hover:text-rose-600">Open PDF</a>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-600">
                                    {{ $document->menuFolder?->pathTitle() ?? $document->dropdown_name }}
                                </td>
                                <td class="max-w-xs px-6 py-4">
                                    <div class="truncate font-semibold text-slate-700" title="{{ $document->original_filename }}">{{ $document->original_filename }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $document->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $document->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-500">{{ $document->created_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex flex-col gap-2">
                                        <button type="button" data-modal-target="notification-edit-{{ $document->id }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.imports.notifications.destroy', $document) }}" method="POST" onsubmit="return confirm('Delete this PDF and remove it from the header dropdown?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm font-semibold text-slate-400">No PDF imports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($notificationDocuments->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $notificationDocuments->links() }}
                </div>
            @endif
        </section>

        @foreach ($resultImports as $import)
            @if ($import->dataset)
                <dialog id="result-edit-{{ $import->id }}" class="w-[min(34rem,calc(100vw-2rem))] rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl">
                    <form action="{{ route('admin.imports.results.update', $import) }}" method="POST" class="p-6">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Edit Result Import</p>
                                <h3 class="mt-1 text-xl font-extrabold text-slate-950">Result Details</h3>
                            </div>
                            <button type="button" data-modal-close class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-bold text-slate-500 hover:text-rose-600">Close</button>
                        </div>
                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Title</label>
                                <input name="title" value="{{ $import->dataset->title }}" maxlength="180" required class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>
                            <div class="grid gap-3 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                                <div><span class="font-bold text-slate-900">File:</span> {{ $import->original_filename }}</div>
                                <div><span class="font-bold text-slate-900">Course:</span> {{ $import->dataset?->course ?? '-' }}</div>
                                <div><span class="font-bold text-slate-900">Rows:</span> {{ number_format($import->total_rows ?? 0) }}</div>
                                <div><span class="font-bold text-slate-900">Status:</span> {{ ucfirst($import->status) }}</div>
                                <div><span class="font-bold text-slate-900">Imported:</span> {{ $import->created_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                            <button type="button" data-modal-close class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">Cancel</button>
                            <button type="submit" class="rounded-xl bg-rose-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-rose-600">Save</button>
                        </div>
                    </form>
                </dialog>
            @endif
        @endforeach

        @foreach ($predictedRankImports as $import)
            @if ($import->analysisDataset)
                <dialog id="analysis-edit-{{ $import->id }}" class="w-[min(34rem,calc(100vw-2rem))] rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl">
                    <form action="{{ route('admin.imports.predicted-rank.update', $import) }}" method="POST" class="p-6">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Edit Predicted Rank Import</p>
                                <h3 class="mt-1 text-xl font-extrabold text-slate-950">Predicted Rank Details</h3>
                            </div>
                            <button type="button" data-modal-close class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-bold text-slate-500 hover:text-rose-600">Close</button>
                        </div>
                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Title</label>
                                <input name="title" value="{{ $import->analysisDataset->title }}" maxlength="180" required class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>
                            <div class="grid gap-3 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                                <div><span class="font-bold text-slate-900">File:</span> {{ $import->original_filename }}</div>
                                <div><span class="font-bold text-slate-900">Course:</span> {{ $import->analysisDataset?->course ?? '-' }}</div>
                                <div><span class="font-bold text-slate-900">Rows:</span> {{ number_format($import->total_rows ?? 0) }}</div>
                                <div><span class="font-bold text-slate-900">Status:</span> {{ ucfirst($import->status) }}</div>
                                <div><span class="font-bold text-slate-900">Imported:</span> {{ $import->created_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                            <button type="button" data-modal-close class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">Cancel</button>
                            <button type="submit" class="rounded-xl bg-rose-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-rose-600">Save</button>
                        </div>
                    </form>
                </dialog>
            @endif
        @endforeach

        @foreach ($notificationDocuments as $document)
            <dialog id="notification-edit-{{ $document->id }}" class="w-[min(36rem,calc(100vw-2rem))] rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl">
                <form action="{{ route('admin.imports.notifications.update', $document) }}" method="POST" class="p-6">
                    @csrf
                    @method('PATCH')
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Edit PDF</p>
                            <h3 class="mt-1 text-xl font-extrabold text-slate-950">PDF Details</h3>
                        </div>
                        <button type="button" data-modal-close class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-bold text-slate-500 hover:text-rose-600">Close</button>
                    </div>
                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Title</label>
                            <input name="title" value="{{ $document->title }}" maxlength="160" required class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Dropdown</label>
                            <select name="menu_folder_id" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                                @foreach ($folderOptions as $folder)
                                    <option value="{{ $folder['id'] }}" @selected((int) ($document->menu_folder_id ?? 0) === (int) $folder['id'])>
                                        {{ str_repeat('-- ', max(0, $folder['depth'] - 1)) }}{{ $folder['path'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Sort Order</label>
                                <input name="sort_order" type="number" min="0" max="999999" value="{{ $document->sort_order }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>
                            <label class="mt-6 inline-flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-rose-500 focus:ring-rose-500" @checked($document->is_active)>
                                Active
                            </label>
                        </div>
                        <div class="grid gap-3 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                            <div><span class="font-bold text-slate-900">File:</span> {{ $document->original_filename }}</div>
                            <div><span class="font-bold text-slate-900">Uploaded:</span> {{ $document->created_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" data-modal-close class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">Cancel</button>
                        <button type="submit" class="rounded-xl bg-rose-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-rose-600">Save</button>
                    </div>
                </form>
            </dialog>
        @endforeach
    </main>

    <script>
        document.querySelectorAll('[data-modal-target]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.dataset.modalTarget);
                if (modal?.showModal) {
                    modal.showModal();
                }
            });
        });

        document.querySelectorAll('dialog').forEach((modal) => {
            modal.querySelectorAll('[data-modal-close]').forEach((button) => {
                button.addEventListener('click', () => modal.close());
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.close();
                }
            });
        });
    </script>
</body>

</html>
