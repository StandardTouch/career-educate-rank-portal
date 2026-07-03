<?php

namespace App\Services;

use App\Models\CourseMapping;
use Illuminate\Support\Str;

class CourseDetectionService
{
    protected array $builtInAliases = [
        'mbbs' => 'MBBS',
        'bds' => 'BDS',
        'dental' => 'BDS',
    ];

    public function detectFromText(string ...$values): ?string
    {
        $text = $this->normalizeSearchText(implode(' ', array_filter($values)));

        if ($text === '') {
            return null;
        }

        foreach ($this->allAliases() as $alias => $course) {
            if (preg_match('/\b' . preg_quote($alias, '/') . '\b/i', $text)) {
                return $course;
            }
        }

        return null;
    }

    public function suggestFromText(string ...$values): ?string
    {
        $text = strtoupper(implode(' ', array_filter($values)));

        if (preg_match_all('/\b([A-Z]{2,8})\b/', $text, $matches)) {
            $ignored = ['NEET', 'UG', 'PG', 'DATA', 'RANK', 'ROUND', 'QUOTA', 'GOVT', 'NRI'];

            foreach ($matches[1] as $candidate) {
                if (! in_array($candidate, $ignored, true)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    public function remember(string $alias, string $course, string $source = 'admin'): void
    {
        $alias = $this->normalizeAlias($alias);
        $course = $this->normalizeCourse($course);

        if ($alias === '' || $course === '') {
            return;
        }

        CourseMapping::updateOrCreate(
            ['alias' => $alias],
            ['course' => $course, 'source' => $source]
        );
    }

    public function courses(): array
    {
        return collect($this->allAliases())
            ->values()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function normalizeCourse(?string $course): string
    {
        $course = trim((string) $course);
        $course = preg_replace('/\s+/', ' ', $course);

        return strtoupper((string) $course);
    }

    protected function allAliases(): array
    {
        $aliases = $this->builtInAliases;

        if (class_exists(CourseMapping::class)) {
            try {
                CourseMapping::query()
                    ->orderBy('alias')
                    ->get(['alias', 'course'])
                    ->each(function (CourseMapping $mapping) use (&$aliases): void {
                        $aliases[$this->normalizeAlias($mapping->alias)] = $this->normalizeCourse($mapping->course);
                    });
            } catch (\Throwable) {
                return $aliases;
            }
        }

        return $aliases;
    }

    protected function normalizeAlias(string $alias): string
    {
        return Str::of($alias)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }

    protected function normalizeSearchText(string $text): string
    {
        return Str::of($text)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }
}
