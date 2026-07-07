@php
    $children = $folder->activeChildren ?? collect();
    $documents = $folder->activeNotificationDocuments ?? collect();
@endphp

@foreach ($documents as $document)
    <a href="{{ route('notifications.view', $document) }}" target="_blank" rel="noopener" class="results-menu-link text-slate-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl px-3 py-2 text-sm transition-colors block">
        {{ \Illuminate\Support\Str::upper($document->title) }}
    </a>
@endforeach

@foreach ($children as $childFolder)
    <details class="results-menu-folder rounded-xl border border-slate-100 bg-slate-50/70" open>
        <summary class="cursor-pointer select-none px-3 py-2 text-sm font-bold text-rose-600">{{ $childFolder->title }}</summary>
        <div class="grid gap-1 px-2 pb-2">
            @include('partials.menu-folder-items', ['folder' => $childFolder])
        </div>
    </details>
@endforeach
