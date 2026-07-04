<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Notification - Career Educate</title>
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
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">PDF Notification Import</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Import Notification</h1>
                <p class="mt-2 text-slate-500 max-w-2xl">
                    Upload a notification PDF. After upload, you can confirm or edit the title before it appears in the Notifications dropdown.
                </p>
            </div>

            <form action="{{ route('notifications.import.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6 mt-6">
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
                    <label for="pdf_file" class="block text-sm font-bold uppercase tracking-wide text-slate-600">Notification PDF</label>
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
        </section>
    </main>
</body>

</html>
