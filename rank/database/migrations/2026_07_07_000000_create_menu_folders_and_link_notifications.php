<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menu_folders')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->unsignedTinyInteger('depth')->default(1)->index();
            $table->integer('sort_order')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['parent_id', 'slug']);
        });

        Schema::table('notification_documents', function (Blueprint $table) {
            $table->foreignId('menu_folder_id')
                ->nullable()
                ->after('dropdown_name')
                ->constrained('menu_folders')
                ->nullOnDelete();
        });

        foreach (['Notifications', 'MBBS Study Abroad'] as $title) {
            $this->ensureRootFolder($title);
        }

        if (Schema::hasColumn('notification_documents', 'dropdown_name')) {
            $dropdowns = DB::table('notification_documents')
                ->select('dropdown_name')
                ->whereNotNull('dropdown_name')
                ->distinct()
                ->pluck('dropdown_name');

            foreach ($dropdowns as $dropdownName) {
                $folderId = $this->ensureRootFolder((string) $dropdownName);

                DB::table('notification_documents')
                    ->where('dropdown_name', $dropdownName)
                    ->whereNull('menu_folder_id')
                    ->update(['menu_folder_id' => $folderId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('notification_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('menu_folder_id');
        });

        Schema::dropIfExists('menu_folders');
    }

    protected function ensureRootFolder(string $title): int
    {
        $title = trim(preg_replace('/\s+/', ' ', $title)) ?: 'Notifications';
        $slug = Str::slug($title) ?: Str::random(8);
        $existing = DB::table('menu_folders')
            ->whereNull('parent_id')
            ->where('slug', $slug)
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('menu_folders')->insertGetId([
            'parent_id' => null,
            'title' => $this->normalizeTitle($title),
            'slug' => $slug,
            'depth' => 1,
            'sort_order' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function normalizeTitle(string $title): string
    {
        $title = Str::title(strtolower(trim(preg_replace('/\s+/', ' ', $title))));

        return str_replace(['Mbbs', 'Bds', 'Neet'], ['MBBS', 'BDS', 'NEET'], $title);
    }
};
