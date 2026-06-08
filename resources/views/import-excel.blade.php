<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Results - Career Educate</title>
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

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Excel Import</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Import NEET Result Sheet</h1>
                <p class="mt-2 text-slate-500 max-w-2xl">
                    Upload an Excel workbook to create or update the table, round table, model, controller, page, route, and year menu entry.
                </p>
            </div>

            <form id="importForm" action="{{ route('import.excel.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6" onsubmit="showLoading()">
                @csrf

                @if (session('status'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="excel_file" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Excel File</label>
                    <input
                        id="excel_file"
                        name="excel_file"
                        type="file"
                        accept=".xlsx"
                        required
                        class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-rose-500 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-rose-600"
                    >
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <button type="submit" class="inline-flex justify-center rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                        Import Excel
                    </button>

                    @if (session('page_url'))
                        <a href="{{ session('page_url') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                            Open Imported Page
                        </a>
                    @endif
                </div>
            </form>
        </section>

@if (session('import_output'))
    <section class="mt-6 rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-sm">
        <div class="text-xs font-bold uppercase tracking-[0.18em]" style="color: #cbd5e1;">Import Log</div>
        <div class="mt-3 max-h-[28rem] overflow-auto text-sm leading-6" style="color: #f8fafc;">
            @foreach (explode("\n", session('import_output')) as $line)
                @php
                    $class = 'text-gray-400';
                    if (Str::contains($line, 'Success')) {
                        $class = 'text-emerald-400';
                    } elseif (Str::contains($line, 'Error') || Str::contains($line, 'Failed')) {
                        $class = 'text-rose-400';
                    } elseif (Str::contains($line, 'Warning')) {
                        $class = 'text-amber-400';
                    }
                @endphp
                <div class="{{ $class }}">{{ $line }}</div>
            @endforeach
        </div>
    </section>
@endif
    </main>
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <svg class="animate-spin h-12 w-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
</div>
</body>

<script>
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}
</script>
</html>
