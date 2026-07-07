<?php

namespace App\Http\Controllers;

use App\Models\MenuFolder;
use App\Models\NotificationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationDocumentController extends Controller
{
    public function create(): View
    {
        $this->ensureDefaultFolders();

        return view('admin.import-notification', [
            'folderOptions' => $this->folderOptions(),
            'parentFolderOptions' => $this->parentFolderOptions(),
            'maxDepth' => MenuFolder::MAX_DEPTH,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $file = $validated['pdf_file'];
        $originalName = $file->getClientOriginalName();
        $storedPath = $file->storeAs(
            'pending-notifications',
            now()->format('YmdHis') . '-' . $this->safeFilename($originalName),
            'public'
        );

        $request->session()->put('pending_notification_document', [
            'stored_path' => $storedPath,
            'original_name' => $originalName,
            'suggested_title' => $this->titleFromFilename($originalName),
        ]);

        return redirect()->route('notifications.import.confirm');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'parent_id' => ['nullable', 'integer', 'exists:menu_folders,id'],
        ]);

        $folder = $this->createFolder(
            $validated['title'],
            isset($validated['parent_id']) ? (int) $validated['parent_id'] : null
        );

        return redirect()
            ->route('notifications.import')
            ->with('status', 'Dropdown "' . $folder->pathTitle() . '" created successfully.');
    }

    public function confirm(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('pending_notification_document');

        if (! is_array($pending) || empty($pending['stored_path']) || ! Storage::disk('public')->exists($pending['stored_path'])) {
            return redirect()->route('notifications.import')
                ->withErrors(['pdf_file' => 'No pending notification PDF was found. Please upload the file again.']);
        }

        return view('admin.import-notification-confirm', [
            'originalName' => (string) ($pending['original_name'] ?? 'Notification.pdf'),
            'suggestedTitle' => (string) ($pending['suggested_title'] ?? 'Notification'),
            'folderOptions' => $this->folderOptions(),
            'parentFolderOptions' => $this->parentFolderOptions(),
            'maxDepth' => MenuFolder::MAX_DEPTH,
        ]);
    }

    public function confirmStore(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('pending_notification_document');

        if (! is_array($pending) || empty($pending['stored_path']) || ! Storage::disk('public')->exists($pending['stored_path'])) {
            return redirect()->route('notifications.import')
                ->withErrors(['pdf_file' => 'No pending notification PDF was found. Please upload the file again.']);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'menu_folder_id' => ['nullable', 'integer', 'exists:menu_folders,id'],
            'new_dropdown_title' => ['nullable', 'string', 'max:80'],
            'new_dropdown_parent_id' => ['nullable', 'integer', 'exists:menu_folders,id'],
        ]);

        $folder = $this->resolveSelectedFolder($validated);

        NotificationDocument::create([
            'title' => trim($validated['title']),
            'original_filename' => (string) ($pending['original_name'] ?? 'Notification.pdf'),
            'stored_path' => (string) $pending['stored_path'],
            'dropdown_name' => $folder->title,
            'menu_folder_id' => $folder->id,
            'is_active' => true,
            'uploaded_by' => $request->user()?->id,
        ]);

        $request->session()->forget('pending_notification_document');

        return redirect()
            ->route('notifications.import')
            ->with('status', 'Notification PDF uploaded successfully.');
    }

    public function show(NotificationDocument $notificationDocument): StreamedResponse
    {
        abort_unless($notificationDocument->is_active, 404);
        abort_unless(Storage::disk('public')->exists($notificationDocument->stored_path), 404);

        return Storage::disk('public')->response(
            $notificationDocument->stored_path,
            $notificationDocument->original_filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    protected function safeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9 _-]+/', '', $name);
        $name = preg_replace('/\s+/', ' ', trim((string) $name));

        return ($name !== '' ? $name : 'notification-' . now()->format('YmdHis')) . '.' . strtolower($extension);
    }

    protected function titleFromFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $name));

        return trim(Str::title(strtolower((string) $name))) ?: 'Notification';
    }

    protected function folderOptions(): array
    {
        $this->ensureDefaultFolders();

        $folders = MenuFolder::with('parent.parent')
            ->orderBy('depth')
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return $folders
            ->map(fn (MenuFolder $folder) => [
                'id' => $folder->id,
                'title' => $folder->title,
                'path' => $folder->pathTitle(),
                'depth' => $folder->depth,
                'can_have_children' => $folder->canHaveChildren(),
            ])
            ->values()
            ->all();
    }

    protected function parentFolderOptions(): array
    {
        return collect($this->folderOptions())
            ->filter(fn (array $folder) => $folder['can_have_children'])
            ->values()
            ->all();
    }

    protected function normalizeDropdownName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        $name = Str::title(strtolower((string) $name));

        return str_replace(['Mbbs', 'Bds', 'Neet'], ['MBBS', 'BDS', 'NEET'], $name);
    }

    protected function resolveSelectedFolder(array $validated): MenuFolder
    {
        $newTitle = trim((string) ($validated['new_dropdown_title'] ?? ''));

        if ($newTitle !== '') {
            $parentId = isset($validated['new_dropdown_parent_id']) && $validated['new_dropdown_parent_id'] !== null
                ? (int) $validated['new_dropdown_parent_id']
                : (isset($validated['menu_folder_id']) ? (int) $validated['menu_folder_id'] : null);

            return $this->createFolder($newTitle, $parentId);
        }

        if (! empty($validated['menu_folder_id'])) {
            return MenuFolder::findOrFail((int) $validated['menu_folder_id']);
        }

        return $this->ensureDefaultFolders()->firstWhere('title', 'Notifications')
            ?? MenuFolder::query()->orderBy('id')->firstOrFail();
    }

    protected function createFolder(string $title, ?int $parentId = null): MenuFolder
    {
        $title = $this->normalizeDropdownName($title);
        $parent = $parentId ? MenuFolder::findOrFail($parentId) : null;
        $depth = $parent ? $parent->depth + 1 : 1;

        if ($depth > MenuFolder::MAX_DEPTH) {
            throw ValidationException::withMessages([
                'parent_id' => 'Dropdown nesting is limited to ' . MenuFolder::MAX_DEPTH . ' levels.',
            ]);
        }

        $slug = MenuFolder::makeSlug($title);

        return DB::transaction(function () use ($parent, $title, $slug, $depth): MenuFolder {
            $existing = MenuFolder::query()
                ->where('parent_id', $parent?->id)
                ->where('slug', $slug)
                ->first();

            if ($existing) {
                return $existing;
            }

            return MenuFolder::create([
                'parent_id' => $parent?->id,
                'title' => $title,
                'slug' => $slug,
                'depth' => $depth,
                'is_active' => true,
            ]);
        });
    }

    protected function ensureDefaultFolders()
    {
        return collect(['Notifications', 'MBBS Study Abroad'])
            ->map(fn (string $title) => $this->createFolder($title));
    }
}
