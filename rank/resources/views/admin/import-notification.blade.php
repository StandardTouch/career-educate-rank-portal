<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import PDF - Career Educate</title>
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

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 mt-12">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 pt-8 pb-5 border-b border-slate-200 mb-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">PDF Import</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Import PDF</h1>
                <p class="mt-2 text-slate-500 max-w-2xl">
                    Upload a PDF, or create dropdown folders first. Dropdown nesting is limited to {{ $maxDepth ?? 3 }} levels to keep the header easy to use.
                </p>
            </div>

            <div class="grid gap-6 p-6 lg:grid-cols-[1fr_0.9fr]">
            <form action="{{ route('notifications.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                    <label for="pdf_file" class="block text-sm font-bold uppercase tracking-wide text-slate-600">PDF File</label>
                    <input
                        id="pdf_file"
                        name="pdf_file"
                        type="file"
                        accept="application/pdf,.pdf"
                        required
                        class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-rose-500 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-rose-600"
                    >
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <button type="submit" class="inline-flex justify-center rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                        Upload PDF
                    </button>
                </div>
            </form>

            <div class="space-y-5">
            <form action="{{ route('notifications.folders.store') }}" method="POST" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                @csrf
                <p class="text-sm font-bold text-slate-900">Create Dropdown</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">Create a main dropdown or choose a parent to create a nested dropdown.</p>

                <div class="mt-4">
                    <label for="folder_title" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Dropdown Title</label>
                    <input
                        id="folder_title"
                        name="title"
                        type="text"
                        maxlength="80"
                        placeholder="Example: Telangana"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                    >
                </div>

                <div class="mt-4">
                    <label for="parent_id" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Parent Dropdown</label>
                    <select
                        id="parent_id"
                        name="parent_id"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                    >
                        <option value="">Create as main dropdown</option>
                        @foreach ($parentFolderOptions ?? [] as $folder)
                            <option value="{{ $folder['id'] }}">{{ str_repeat('-- ', max(0, $folder['depth'] - 1)) }}{{ $folder['path'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="mt-5 inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                    Create Dropdown
                </button>
            </form>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-sm font-bold text-slate-900">Existing Dropdowns</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">Delete a custom dropdown to remove it, all sub-dropdowns, and all PDFs inside it.</p>

                <div class="mt-4 max-h-80 space-y-2 overflow-y-auto pr-1">
                    @foreach ($folderOptions ?? [] as $folder)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800">{{ str_repeat('-- ', max(0, $folder['depth'] - 1)) }}{{ $folder['path'] }}</p>
                                <p class="mt-0.5 text-xs font-semibold text-slate-400">{{ $folder['document_count'] ?? 0 }} PDF file(s)</p>
                            </div>
                            @if ($folder['can_delete'] ?? false)
                                <form action="{{ route('notifications.folders.destroy', $folder['id']) }}" method="POST" onsubmit="return confirm('Delete this dropdown, all sub-dropdowns, and all PDFs inside it?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <span class="shrink-0 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-400">Default</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            </div>
            </div>
        </section>
    </main>
</body>

</html>
