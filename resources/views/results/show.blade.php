<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $dataset->title }} - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    @php
        $selectedFee = request('fee_max');
        $feeMax = max(0, (int) ($maxFee ?? 0));
        $feeSliderValue = $selectedFee !== null && $selectedFee !== '' ? (int) $selectedFee : $feeMax;
        $selectedRounds = $selectedRounds ?? ['overall'];
        $selectedFilters = $selectedFilters ?? [];
        $selectedSheet = $sheetOptions->first(function ($sheet) use ($selectedRounds) {
            return in_array('overall', $selectedRounds, true)
                ? $sheet->sheet_type === 'overall'
                : in_array((string) $sheet->round_id, array_map('strval', $selectedRounds), true);
        });
        $selectedRoundLabel = count($selectedRounds) > 1
            ? count($selectedRounds) . ' selected'
            : ($selectedSheet?->sheet_name ?? 'Overall');
        $columnOptions = [
            'state_name' => 'State Name',
            'college_name' => 'College Name',
            'category' => 'Category',
            'round_name' => 'Round Name',
            'local_area' => 'Local Area',
            'total_seats' => 'Total Seats',
            'gen_closing_rank' => 'Gen Closing Rank',
            'gen_closing_mark' => 'Gen Closing Mark',
            'fem_closing_rank' => 'Fem Closing Rank',
            'fem_closing_mark' => 'Fem Closing Mark',
            'tuition_fee' => 'Tuition Fee',
        ];
    @endphp

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-10">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">
                        {{ $dataset->course ?? 'NEET' }} {{ $dataset->year }}
                    </p>
                    <h1 class="mt-2 text-3xl font-extrabold text-slate-950">{{ $dataset->title }}</h1>
                    <p class="mt-2 text-sm text-slate-500">
                        Dynamic DB-driven result page. Overall records use no round; round sheets appear below by sheet name.
                    </p>
                </div>
                <div class="text-sm font-semibold text-slate-500">
                    {{ number_format($records->total()) }} matching records
                </div>
            </div>

            <form method="GET" action="{{ route('results.show', $dataset) }}" class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Search</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="College, quota, category"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Round</label>
                    <div class="searchable-select relative mt-2" data-empty-label="Overall" data-exclusive-value="overall">
                        <button type="button" class="searchable-select-trigger flex w-full items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-medium text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <span class="searchable-select-label">{{ $selectedRoundLabel }}</span>
                            <span class="text-slate-400">v</span>
                        </button>
                        <div class="searchable-select-panel hidden absolute left-0 right-0 top-full z-40 mt-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                            <input type="search" class="searchable-select-search w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20" placeholder="Search rounds...">
                            <div class="mt-2 max-h-64 overflow-y-auto">
                                @forelse ($sheetOptions as $sheet)
                                    @php $roundValue = $sheet->sheet_type === 'overall' ? 'overall' : (string) $sheet->round_id; @endphp
                                    <label class="searchable-select-option flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">
                                        <input type="checkbox" name="round_id[]" value="{{ $roundValue }}" class="searchable-select-checkbox rounded border-slate-300 text-rose-500 focus:ring-rose-500" @checked(in_array($roundValue, array_map('strval', $selectedRounds), true))>
                                        <span>{{ $sheet->sheet_name }}</span>
                                    </label>
                                @empty
                                    <label class="searchable-select-option flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">
                                        <input type="checkbox" name="round_id[]" value="overall" class="searchable-select-checkbox rounded border-slate-300 text-rose-500 focus:ring-rose-500" @checked(in_array('overall', $selectedRounds, true))>
                                        <span>Overall</span>
                                    </label>
                                    @foreach ($rounds as $round)
                                        <label class="searchable-select-option flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">
                                            <input type="checkbox" name="round_id[]" value="{{ $round->id }}" class="searchable-select-checkbox rounded border-slate-300 text-rose-500 focus:ring-rose-500" @checked(in_array((string) $round->id, array_map('strval', $selectedRounds), true))>
                                            <span>{{ $round->name }}</span>
                                        </label>
                                    @endforeach
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                @foreach (['college_name' => 'College', 'quota' => 'Quota', 'category' => 'Category', 'local_area' => 'Local Area'] as $field => $label)
                    @php
                        $fieldSelected = array_map('strval', $selectedFilters[$field] ?? []);
                        $fieldLabel = count($fieldSelected) === 0
                            ? 'Any'
                            : (count($fieldSelected) === 1 ? $fieldSelected[0] : count($fieldSelected) . ' selected');
                    @endphp
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ $label }}</label>
                        <div class="searchable-select relative mt-2" data-empty-label="Any">
                            <button type="button" class="searchable-select-trigger flex w-full items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-medium text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                                <span class="searchable-select-label">{{ $fieldLabel }}</span>
                                <span class="text-slate-400">v</span>
                            </button>
                            <div class="searchable-select-panel hidden absolute left-0 right-0 top-full z-40 mt-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <input type="search" class="searchable-select-search w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20" placeholder="Search {{ strtolower($label) }}...">
                                <div class="mt-2 max-h-64 overflow-y-auto">
                                    @foreach ($filterValues[$field] as $value)
                                        <label class="searchable-select-option flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">
                                            <input type="checkbox" name="{{ $field }}[]" value="{{ $value }}" class="searchable-select-checkbox rounded border-slate-300 text-rose-500 focus:ring-rose-500" @checked(in_array((string) $value, $fieldSelected, true))>
                                            <span>{{ $value }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Your Rank</label>
                    <input type="number" name="rank" value="{{ request('rank') }}" placeholder="Show closing rank >= this"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div class="lg:col-span-4">
                    <div class="flex items-center justify-between gap-4">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700">Fee Range (₹):</label>
                        <div class="text-sm font-bold text-rose-500">Selected: <span id="fee-selected-label">{{ $selectedFee !== null && $selectedFee !== '' ? '₹' . number_format((int) $selectedFee) : 'Any' }}</span></div>
                    </div>
                    <input type="hidden" name="fee_max" id="fee_max_input" value="{{ $selectedFee }}">
                    <input type="range" id="fee_max_slider" min="0" max="{{ $feeMax }}" step="1000" value="{{ $feeSliderValue }}"
                        class="fee-range mt-5 w-full accent-rose-500" @disabled($feeMax === 0)>
                    <div class="mt-2 flex justify-between text-xs font-bold text-slate-400">
                        <span>Min: ₹0</span>
                        <span>Max: ₹{{ number_format($feeMax) }}</span>
                    </div>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full rounded-xl bg-rose-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-600">
                        Apply Filters
                    </button>
                    <a href="{{ route('results.show', $dataset) }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-rose-300 hover:text-rose-600">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-900">Results</p>
                    <p class="text-xs text-slate-500">Choose which columns are visible in the table.</p>
                </div>
                <div class="relative">
                    <button type="button" id="column-toggle-trigger" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-bold text-slate-700 hover:border-rose-300 hover:text-rose-600">
                        Columns
                        <span class="text-xs">v</span>
                    </button>
                    <div id="column-toggle-panel" class="hidden fixed z-50 w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                        @foreach ($columnOptions as $columnKey => $columnLabel)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                <input type="checkbox" class="column-toggle rounded border-slate-300 text-rose-500 focus:ring-rose-500" value="{{ $columnKey }}" checked>
                                <span class="whitespace-nowrap">{{ $columnLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th data-col="state_name" class="px-4 py-3 text-left">State Name</th>
                            <th data-col="college_name" class="px-4 py-3 text-left">College Name</th>
                            <th data-col="category" class="px-4 py-3 text-left">Category</th>
                            <th data-col="round_name" class="px-4 py-3 text-left">Round Name</th>
                            <th data-col="local_area" class="px-4 py-3 text-left">Local Area</th>
                            <th data-col="total_seats" class="px-4 py-3 text-right">
                                Total Seats
                                <span class="block text-[11px] font-extrabold text-rose-500">{{ $totalSeats ?? 0 }}</span>
                            </th>
                            <th data-col="gen_closing_rank" class="px-4 py-3 text-right">Gen Closing Rank</th>
                            <th data-col="gen_closing_mark" class="px-4 py-3 text-right">Gen Closing Mark</th>
                            <th data-col="fem_closing_rank" class="px-4 py-3 text-right">Fem Closing Rank</th>
                            <th data-col="fem_closing_mark" class="px-4 py-3 text-right">Fem Closing Mark</th>
                            <th data-col="tuition_fee" class="px-4 py-3 text-right">Tuition Fee</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($records as $record)
                            @php
                                $payload = is_array($record->raw_payload) ? $record->raw_payload : (json_decode((string) $record->raw_payload, true) ?: []);
                                $stateName = $payload['state_name'] ?? '-';
                                $roundName = $record->round?->name ?? ($selectedSheet?->sheet_name ?? 'Overall');
                                $femaleClosingRank = $payload['female_closing_rank'] ?? null;
                                $femaleClosingMark = $payload['female_marks'] ?? null;
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td data-col="state_name" class="px-4 py-3 font-semibold text-slate-600">{{ $stateName }}</td>
                                <td data-col="college_name" class="px-4 py-3 font-bold text-slate-900">{{ $record->college_name ?? '-' }}</td>
                                <td data-col="category" class="px-4 py-3">{{ $record->category ?? '-' }}</td>
                                <td data-col="round_name" class="px-4 py-3">{{ $roundName }}</td>
                                <td data-col="local_area" class="px-4 py-3">{{ $record->local_area ?? '-' }}</td>
                                <td data-col="total_seats" class="px-4 py-3 text-right">{{ $record->seats !== null ? $record->seats : '-' }}</td>
                                <td data-col="gen_closing_rank" class="px-4 py-3 text-right font-bold text-rose-600">{{ $record->closing_rank !== null ? $record->closing_rank : '-' }}</td>
                                <td data-col="gen_closing_mark" class="px-4 py-3 text-right">{{ $record->marks !== null ? number_format((float) $record->marks, 2) : '-' }}</td>
                                <td data-col="fem_closing_rank" class="px-4 py-3 text-right">{{ $femaleClosingRank !== null && $femaleClosingRank !== '' ? (float) $femaleClosingRank : '-' }}</td>
                                <td data-col="fem_closing_mark" class="px-4 py-3 text-right">{{ $femaleClosingMark !== null && $femaleClosingMark !== '' ? number_format((float) $femaleClosingMark, 2) : '-' }}</td>
                                <td data-col="tuition_fee" class="px-4 py-3 text-right">{{ $record->fees !== null ? (int) $record->fees : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">
                                    No records found for these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-4">
                {{ $records->links() }}
            </div>
        </section>
    </main>

    <style>
        .fee-range {
            height: 0.5rem;
            cursor: pointer;
        }

        .fee-range::-webkit-slider-runnable-track {
            height: 0.5rem;
            border-radius: 9999px;
            background: #f1f5f9;
        }

        .fee-range::-webkit-slider-thumb {
            margin-top: -0.35rem;
        }

        .fee-range::-moz-range-track {
            height: 0.5rem;
            border-radius: 9999px;
            background: #f1f5f9;
        }
    </style>

    <script>
        (function () {
            const currency = new Intl.NumberFormat('en-IN');
            const feeSlider = document.getElementById('fee_max_slider');
            const feeInput = document.getElementById('fee_max_input');
            const feeLabel = document.getElementById('fee-selected-label');

            feeSlider?.addEventListener('input', () => {
                feeInput.value = feeSlider.value;
                feeLabel.textContent = `₹${currency.format(Number(feeSlider.value || 0))}`;
            });

            document.querySelectorAll('.searchable-select').forEach((select) => {
                const trigger = select.querySelector('.searchable-select-trigger');
                const panel = select.querySelector('.searchable-select-panel');
                const search = select.querySelector('.searchable-select-search');
                const label = select.querySelector('.searchable-select-label');
                const checkboxes = Array.from(select.querySelectorAll('.searchable-select-checkbox'));

                function updateSelectLabel() {
                    const selected = checkboxes
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.closest('.searchable-select-option')?.textContent.trim())
                        .filter(Boolean);

                    label.textContent = selected.length === 0
                        ? (select.dataset.emptyLabel || 'Any')
                        : (selected.length === 1 ? selected[0] : `${selected.length} selected`);
                }

                trigger?.addEventListener('click', (event) => {
                    event.stopPropagation();
                    document.querySelectorAll('.searchable-select').forEach((openSelect) => {
                        if (openSelect !== select) {
                            openSelect.style.zIndex = '';
                            openSelect.querySelector('.searchable-select-panel')?.classList.add('hidden');
                        }
                    });

                    select.style.zIndex = panel.classList.contains('hidden') ? '80' : '';
                    panel.classList.toggle('hidden');
                    search?.focus();
                });

                search?.addEventListener('input', () => {
                    const term = search.value.trim().toLowerCase();
                    select.querySelectorAll('.searchable-select-option').forEach((option) => {
                        option.hidden = term !== '' && !option.textContent.toLowerCase().includes(term);
                    });
                });

                checkboxes.forEach((checkbox) => checkbox.addEventListener('change', () => {
                    const exclusiveValue = select.dataset.exclusiveValue;

                    if (exclusiveValue && checkbox.checked) {
                        if (checkbox.value === exclusiveValue) {
                            checkboxes.forEach((otherCheckbox) => {
                                if (otherCheckbox !== checkbox) otherCheckbox.checked = false;
                            });
                        } else {
                            checkboxes
                                .filter((otherCheckbox) => otherCheckbox.value === exclusiveValue)
                                .forEach((otherCheckbox) => {
                                    otherCheckbox.checked = false;
                                });
                        }
                    }

                    updateSelectLabel();
                }));
                updateSelectLabel();
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('.searchable-select')) {
                    document.querySelectorAll('.searchable-select').forEach((select) => {
                        select.style.zIndex = '';
                        select.querySelector('.searchable-select-panel')?.classList.add('hidden');
                    });
                }
            });

            const columnTrigger = document.getElementById('column-toggle-trigger');
            const columnPanel = document.getElementById('column-toggle-panel');
            const columnToggles = Array.from(document.querySelectorAll('.column-toggle'));
            const storageKey = `rank-columns-{{ $dataset->slug }}`;

            function applyColumnVisibility() {
                const visible = Object.fromEntries(columnToggles.map((toggle) => [toggle.value, toggle.checked]));
                localStorage.setItem(storageKey, JSON.stringify(visible));

                Object.entries(visible).forEach(([column, isVisible]) => {
                    document.querySelectorAll(`[data-col="${column}"]`).forEach((cell) => {
                        cell.classList.toggle('hidden', !isVisible);
                    });
                });
            }

            try {
                const savedColumns = JSON.parse(localStorage.getItem(storageKey) || '{}');
                columnToggles.forEach((toggle) => {
                    if (Object.prototype.hasOwnProperty.call(savedColumns, toggle.value)) {
                        toggle.checked = savedColumns[toggle.value];
                    }
                });
            } catch (error) {
                localStorage.removeItem(storageKey);
            }

            columnTrigger?.addEventListener('click', (event) => {
                event.stopPropagation();
                if (columnPanel?.classList.contains('hidden')) {
                    const rect = columnTrigger.getBoundingClientRect();
                    const width = columnPanel.offsetWidth || 288;
                    const padding = 16;
                    const left = Math.min(
                        Math.max(padding, rect.left + (rect.width / 2) - (width / 2)),
                        window.innerWidth - width - padding
                    );

                    columnPanel.style.top = `${rect.bottom + 10}px`;
                    columnPanel.style.left = `${left}px`;
                    columnPanel.style.right = 'auto';
                    columnPanel.style.maxHeight = `${Math.max(220, window.innerHeight - rect.bottom - 24)}px`;
                    columnPanel.style.overflowY = 'auto';
                }
                columnPanel?.classList.toggle('hidden');
            });

            columnToggles.forEach((toggle) => toggle.addEventListener('change', applyColumnVisibility));
            applyColumnVisibility();

            document.addEventListener('click', (event) => {
                if (!event.target.closest('#column-toggle-panel') && !event.target.closest('#column-toggle-trigger')) {
                    columnPanel?.classList.add('hidden');
                }
            });
        })();
    </script>
</body>

</html>
