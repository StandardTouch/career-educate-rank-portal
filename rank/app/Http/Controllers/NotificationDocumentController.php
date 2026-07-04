<?php

namespace App\Http\Controllers;

use App\Models\NotificationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationDocumentController extends Controller
{
    public function create(): View
    {
        return view('admin.import-notification');
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
            'dropdownOptions' => $this->dropdownOptions(),
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
            'dropdown_name' => ['required', 'string', 'max:80'],
        ]);

        NotificationDocument::create([
            'title' => trim($validated['title']),
            'original_filename' => (string) ($pending['original_name'] ?? 'Notification.pdf'),
            'stored_path' => (string) $pending['stored_path'],
            'dropdown_name' => $this->normalizeDropdownName($validated['dropdown_name']),
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

    protected function dropdownOptions(): array
    {
        $defaults = ['Notifications', 'MBBS Study Abroad'];

        $existing = NotificationDocument::query()
            ->whereNotNull('dropdown_name')
            ->pluck('dropdown_name')
            ->filter()
            ->all();

        return collect([...$defaults, ...$existing])
            ->map(fn ($name) => $this->normalizeDropdownName((string) $name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeDropdownName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return Str::title(strtolower((string) $name));
    }
}
