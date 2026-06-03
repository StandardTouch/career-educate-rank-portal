<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>All India Quota MBBS - 2025 Cutoff Analysis</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- jQuery (Needed for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        #column-visibility-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .colvis-checkbox {
            display: none;
        }

        .colvis-button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .colvis-item.active .colvis-button {
            background: #f43f5e; 
            border-color: #f43f5e;
            color: white;
        }

        .colvis-button:hover {
            border-color: #f43f5e;
        }

        /* Custom scrollbar for modals */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* DataTables Tailwnd Overrides */
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.375rem 2rem 0.375rem 0.75rem;
            outline: none;
            background-color: white;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.375rem 0.75rem;
            margin-left: 0.5rem;
            outline: none;
            width: 250px;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #f43f5e !important;
            color: white !important;
            border: 1px solid #f43f5e !important;
            border-radius: 0.375rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            color: black !important;
            border: 1px solid #cbd5e1 !important;
        }

        table.dataTable {
            border-collapse: collapse !important;
            border-spacing: 0;
            width: 100% !important;
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        table.dataTable thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        table.dataTable tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 0.875rem;
        }

        table.dataTable tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
</head>

<body class="min-h-screen text-slate-800 flex flex-col">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-15 h-15">
            </div>

            <div class="flex items-center gap-4">
                <div
                    class="hidden sm:flex items-center gap-1.5 text-sm text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-full">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    NEET 2025 Live Data
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Breadcrumbs -->
        <nav class="flex text-sm text-slate-500 mb-4 items-center gap-2">
            <span class="hover:text-slate-800 transition-colors cursor-pointer">🏠 Guidance</span>
            <span class="text-slate-300">/</span>
            <span class="hover:text-slate-800 transition-colors cursor-pointer">BiPC</span>
            <span class="text-slate-300">/</span>
            <span class="text-slate-800 font-medium">All India Quota MBBS</span>
        </nav>

        <!-- Page Title -->
        <div class="text-center my-6">
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900">
                All India Quota MBBS - 2025 Analysis
            </h1>
            <p class="text-slate-500 mt-2 text-sm sm:text-base">
                Find eligible medical colleges based on closing ranks, quotas, rounds, and local areas.
            </p>
        </div>

        <!-- Filter Panel -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">

            <!-- Rank Input Field -->
            <div>
                <label for="rank-input"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                    Enter your rank    
                </label>
                <div class="relative">
                    <input type="number" id="rank-input" placeholder="Enter your NEET 2025 All India rank..."
                        class="w-full text-lg border border-slate-200 rounded-xl px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all placeholder-slate-400">
                </div>
            </div>

            <!-- Custom Filters Group -->
            <div class="space-y-4">

                <!-- Colleges Filter Trigger -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                        Colleges
                    </label>
                    <button type="button" onclick="openModal('colleges')"
                        class="w-full bg-slate-50 border border-slate-200 hover:border-slate-300 transition-all rounded-xl p-4 flex justify-between items-center text-left">
                        <div class="flex items-center gap-2">
                            <span
                                class="bg-slate-200 text-slate-700 font-medium text-xs px-2.5 py-1 rounded-md">Colleges</span>
                            <span id="colleges-summary" class="text-slate-700 font-medium text-sm">Any Colleges</span>
                        </div>
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Row: Quotas and Rounds -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Quotas Filter Trigger -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            Quotas
                        </label>
                        <button type="button" onclick="openModal('quotas')"
                            class="w-full bg-slate-50 border border-slate-200 hover:border-slate-300 transition-all rounded-xl p-4 flex justify-between items-center text-left">
                            <div class="flex items-center gap-2">
                                <span
                                    class="bg-slate-200 text-slate-700 font-medium text-xs px-2.5 py-1 rounded-md">Quotas</span>
                                <span id="quotas-summary" class="text-slate-700 font-medium text-sm">OPEN</span>
                            </div>
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <!-- Active Tags for Quotas -->
                        <div id="quotas-tags-container" class="flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <!-- Rounds Filter Trigger -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            Rounds
                        </label>
                        <button type="button" onclick="openModal('rounds')"
                            class="w-full bg-slate-50 border border-slate-200 hover:border-slate-300 transition-all rounded-xl p-4 flex justify-between items-center text-left">
                            <div class="flex items-center gap-2">
                                <span
                                    class="bg-slate-200 text-slate-700 font-medium text-xs px-2.5 py-1 rounded-md">Rounds</span>
                                <span id="rounds-summary" class="text-slate-700 font-medium text-sm">Over All</span>
                            </div>
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                </div>

                <!-- Local Areas Filter Trigger -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                        Local Areas
                    </label>
                    <button type="button" onclick="openModal('local_areas')"
                        class="w-full bg-slate-50 border border-slate-200 hover:border-slate-300 transition-all rounded-xl p-4 flex justify-between items-center text-left">
                        <div class="flex items-center gap-2">
                            <span class="bg-slate-200 text-slate-700 font-medium text-xs px-2.5 py-1 rounded-md">Local
                                Areas</span>
                            <span id="local_areas-summary" class="text-slate-700 font-medium text-sm">All Over
                                India</span>
                        </div>
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <!-- Active Tags for Local Areas -->
                    <div id="local_areas-tags-container" class="flex flex-wrap gap-2 mt-2"></div>
                </div>

            </div>

            <!-- Range Slider -->
            <div class="space-y-3 pt-2">
                <div class="flex justify-between items-center">
                    <span class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">
                        Fee Range (₹):
                    </span>
                    <span class="text-rose-500 font-bold text-base" id="fee-selected-text">
                        Selected: ₹{{ number_format($maxFee) }}
                    </span>
                </div>
                <input type="range" id="fee-slider" min="0" max="{{ $maxFee }}"
                    value="{{ $maxFee }}" step="1000"
                    class="w-full h-2 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-rose-500">
                <div class="flex justify-between text-xs font-semibold text-slate-400">
                    <span>Min: ₹0</span>
                    <span id="fee-max-label">Max: ₹{{ number_format($maxFee) }}</span>
                </div>
            </div>

            <!-- Get Analysis Button -->
            <div>
                <button type="button" id="btn-get-analysis"
                    class="w-full bg-rose-500 hover:bg-rose-600 active:scale-[0.99] text-white font-bold text-base py-4 px-6 rounded-2xl transition-all shadow-lg hover:shadow-xl hover:shadow-rose-500/10 flex items-center justify-center gap-2">
                    ⚡ Get Analysis
                </button>
            </div>

        </div>

        <!-- Analysis Results Container -->
        <div id="results-panel" class="mt-8 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hidden">

            <!-- Result Title Header -->
            <div class="text-center pb-6 border-b border-slate-100 mb-6">
                <h3 class="text-xl font-bold text-slate-800">All India Quota MBBS</h3>
                <p class="text-sm text-slate-500 mt-1" id="results-summary-text">
                    [Rounds: Over All, Categories: OPEN, Local Areas: All Over India]
                </p>
            </div>

            <!-- Results Summary Metrics -->
            <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
                <div class="text-slate-600 font-medium text-sm">
                    Results (<span id="results-count" class="font-bold text-rose-500">0</span> entries found):
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-x-auto w-full">
                <table id="analysis-table" class="display w-full">
                    <thead>
                        <tr>
                            <th>State Name</th>
                            <th>College Name</th>
                            <th>Category</th>
                            <th>Round Name</th>
                            <th>Local Area</th>
                            <th>Total Seats <span style="font-size: 10px;" id="total-seats-count">({{ $seatsCount }})</span></th>
                            <th>GEN Closing Rank</th>
                            <th>GEN Closing Mark</th>
                            <th>FEM Closing Rank</th>
                            <th>FEM Closing Mark</th>
                            <th>Tuition Fee</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 text-xs py-6 border-t border-slate-900 mt-12">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                version 2.2.9 | &copy; 2026 Shaheen Group. All rights reserved.
            </div>
            <div class="flex gap-4">
                <a href="#" class="hover:text-white transition-colors">Terms and Conditions</a>
                <span>|</span>
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
            </div>
        </div>
    </footer>


    <!-- ==============================================
         FILTER SELECTOR MODAL DIALOG OVERLAYS
         ============================================== -->

    <!-- Colleges Modal -->
    <div id="modal-colleges" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('colleges')"></div>
        <!-- Container -->
        <div
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6 max-h-[85vh] flex flex-col z-10 animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900">Select Colleges</h3>
                <button type="button" onclick="clearAllOptions('colleges')"
                    class="text-xs font-semibold text-rose-500 hover:text-rose-600 transition-colors">
                    Clear All
                </button>
            </div>
            <!-- Search bar -->
            <div class="mt-4 relative">
                <span
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">🔍</span>
                <input type="text" id="search-colleges" placeholder="Search colleges..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
            </div>
            <!-- Scrollable List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar mt-4 space-y-2 pr-1">
                <!-- Any Colleges item -->
                <label
                    class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 border border-transparent cursor-pointer transition-all">
                    <span class="text-sm font-semibold text-slate-800">Any Colleges</span>
                    <input type="checkbox" id="chk-any-colleges" onchange="toggleAnySelectAll('colleges')"
                        class="rounded border-slate-300 text-rose-500 focus:ring-rose-500 w-4.5 h-4.5">
                </label>
                <!-- Separator -->
                <div class="border-t border-slate-100 my-2"></div>
                <!-- Dynamic Items -->
                <div id="list-colleges" class="space-y-1.5">
                    @foreach ($colleges as $college)
                        <label
                            class="item-college flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 cursor-pointer transition-all"
                            data-name="{{ strtolower($college) }}">
                            <span class="text-xs sm:text-sm text-slate-700 pr-4 select-none">{{ $college }}</span>
                            <input type="checkbox" name="colleges[]" value="{{ $college }}"
                                onchange="handleIndividualChange('colleges')"
                                class="checkbox-college rounded border-slate-300 text-rose-500 focus:ring-rose-500 w-4 h-4">
                        </label>
                    @endforeach
                </div>
            </div>
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4 mt-4">
                <button type="button" onclick="closeModal('colleges')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-500 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="confirmSelections('colleges')"
                    class="px-5 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Quotas Modal -->
    <div id="modal-quotas" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('quotas')"></div>
        <!-- Container -->
        <div
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 max-h-[85vh] flex flex-col z-10 animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900">Select Quotas</h3>
                <button type="button" onclick="clearAllOptions('quotas')"
                    class="text-xs font-semibold text-rose-500 hover:text-rose-600 transition-colors">
                    Clear All
                </button>
            </div>
            <!-- Search bar -->
            <div class="mt-4 relative">
                <span
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">🔍</span>
                <input type="text" id="search-quotas" placeholder="Search quotas..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
            </div>
            <!-- Scrollable List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar mt-4 space-y-2 pr-1">
                <!-- Any Quotas item -->
                <label
                    class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 border border-transparent cursor-pointer transition-all">
                    <span class="text-sm font-semibold text-slate-800">Any Quotas</span>
                    <input type="checkbox" id="chk-any-quotas" onchange="toggleAnySelectAll('quotas')"
                        class="rounded border-slate-300 text-rose-500 focus:ring-rose-500 w-4.5 h-4.5">
                </label>
                <!-- Separator -->
                <div class="border-t border-slate-100 my-2"></div>
                <!-- Dynamic Items -->
                <div id="list-quotas" class="space-y-1.5">
                    @foreach ($quotas as $quota)
                        <label
                            class="item-quota flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 cursor-pointer transition-all"
                            data-name="{{ strtolower($quota) }}">
                            <span class="text-sm text-slate-700 select-none">{{ $quota }}</span>
                            <input type="checkbox" name="quotas[]" value="{{ $quota }}"
                                onchange="handleIndividualChange('quotas')"
                                class="checkbox-quota rounded border-slate-300 text-rose-500 focus:ring-rose-500 w-4 h-4">
                        </label>
                    @endforeach
                </div>
            </div>
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4 mt-4">
                <button type="button" onclick="closeModal('quotas')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-500 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="confirmSelections('quotas')"
                    class="px-5 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Rounds Modal -->
    <div id="modal-rounds" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('rounds')"></div>
        <!-- Container -->
        <div
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 max-h-[85vh] flex flex-col z-10 animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900">Select Rounds</h3>
                <button type="button" onclick="clearAllOptions('rounds')"
                    class="text-xs font-semibold text-rose-500 hover:text-rose-600 transition-colors">
                    Clear All
                </button>
            </div>
            <!-- Search bar -->
            <div class="mt-4 relative">
                <span
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">🔍</span>
                <input type="text" id="search-rounds" placeholder="Search rounds..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
            </div>
            <!-- Scrollable List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar mt-4 space-y-2 pr-1">
                <!-- Over All (Any) Rounds item -->
                <label
                    class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 border border-transparent cursor-pointer transition-all">
                    <span class="text-sm font-semibold text-slate-800">Over All</span>
                    <input type="checkbox" id="chk-any-rounds" onchange="toggleAnySelectAll('rounds')"
                        class="rounded border-slate-300 text-rose-500 focus:ring-rose-500 w-4.5 h-4.5">
                </label>
                <!-- Separator -->
                <div class="border-t border-slate-100 my-2"></div>
                <!-- Dynamic Items -->
                <div id="list-rounds" class="space-y-1.5">
                    @foreach ($rounds as $round)
                        <label
                            class="item-round flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 cursor-pointer transition-all"
                            data-name="{{ strtolower($round->name) }}">
                            <span class="text-sm text-slate-700 select-none">{{ $round->name }}</span>
                            <input type="checkbox" name="rounds[]" value="{{ $round->id }}"
                                data-name="{{ $round->name }}" onchange="handleIndividualChange('rounds')"
                                class="checkbox-round rounded border-slate-300 text-rose-500 focus:ring-rose-500 w-4 h-4">
                        </label>
                    @endforeach
                </div>
            </div>
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4 mt-4">
                <button type="button" onclick="closeModal('rounds')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-500 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="confirmSelections('rounds')"
                    class="px-5 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Local Areas Modal -->
    <div id="modal-local_areas" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('local_areas')"></div>
        <!-- Container -->
        <div
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 max-h-[85vh] flex flex-col z-10 animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900">Select Local Areas</h3>
                <button type="button" onclick="clearAllOptions('local_areas')"
                    class="text-xs font-semibold text-rose-500 hover:text-rose-600 transition-colors">
                    Clear All
                </button>
            </div>
            <!-- Search bar -->
            <div class="mt-4 relative">
                <span
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">🔍</span>
                <input type="text" id="search-local_areas" placeholder="Search local areas..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
            </div>
            <!-- Scrollable List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar mt-4 space-y-2 pr-1">
                <!-- All Over India item -->
                <label
                    class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 border border-transparent cursor-pointer transition-all">
                    <span class="text-sm font-semibold text-slate-800">All Over India</span>
                    <input type="checkbox" id="chk-any-local_areas" onchange="toggleAnySelectAll('local_areas')"
                        class="rounded border-slate-300 text-rose-500 focus:ring-rose-500 w-4.5 h-4.5">
                </label>
                <!-- Separator -->
                <div class="border-t border-slate-100 my-2"></div>
                <!-- Dynamic Items -->
                <div id="list-local_areas" class="space-y-1.5">
                    @foreach ($localAreas as $area)
                        <label
                            class="item-local_area flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 cursor-pointer transition-all"
                            data-name="{{ strtolower($area) }}">
                            <span class="text-sm text-slate-700 select-none">{{ $area }}</span>
                            <input type="checkbox" name="local_areas[]" value="{{ $area }}"
                                onchange="handleIndividualChange('local_areas')"
                                class="checkbox-local_area rounded border-slate-300 text-rose-500 focus:ring-rose-500 w-4 h-4">
                        </label>
                    @endforeach
                </div>
            </div>
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4 mt-4">
                <button type="button" onclick="closeModal('local_areas')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-500 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="confirmSelections('local_areas')"
                    class="px-5 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">
                    Confirm
                </button>
            </div>
        </div>
    </div>


    <!-- ==============================================
         JAVASCRIPT LOGIC AND DATA SYNCHRONIZATION
         ============================================== -->
    <script>
        // Track states for filters
        const filterState = {
            colleges: {
                confirmed: ['any'], // 'any' indicates no filter / all
                temp: ['any']
            },
            quotas: {
                confirmed: ['OPEN'], // Default set to OPEN as shown in screenshot
                temp: ['OPEN']
            },
            rounds: {
                confirmed: ['any'], // 'any' maps to Over All
                temp: ['any']
            },
            local_areas: {
                confirmed: ['any'], // 'any' maps to All Over India
                temp: ['any']
            }
        };

        // Open Modal Dialog
        function openModal(key) {
            const modal = document.getElementById(`modal-${key}`);
            if (!modal) return;

            // Sync temp state with confirmed state before showing
            filterState[key].temp = [...filterState[key].confirmed];

            // Check checkboxes based on temp state
            updateModalCheckboxUI(key);

            // Reset modal search field
            const searchInput = document.getElementById(`search-${key}`);
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
            }

            modal.classList.remove('hidden');
        }

        // Close Modal Dialog
        function closeModal(key) {
            const modal = document.getElementById(`modal-${key}`);
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Check/Uncheck UI in modal based on temp state
        function updateModalCheckboxUI(key) {
            const temp = filterState[key].temp;
            const anyCheckbox = document.getElementById(`chk-any-${key}`);
            const normalCheckboxes = document.querySelectorAll(
                `.checkbox-${key === 'local_areas' ? 'local_area' : key.slice(0, -1)}`);

            if (temp.includes('any') || temp.length === 0) {
                anyCheckbox.checked = true;
                normalCheckboxes.forEach(cb => {
                    cb.checked = true;
                    cb.disabled = true;
                });
            } else {
                anyCheckbox.checked = false;
                normalCheckboxes.forEach(cb => {
                    cb.disabled = false;
                    cb.checked = temp.includes(cb.value);
                });
            }
        }

        // Toggle select-all ("Any" checkbox) handler
        function toggleAnySelectAll(key) {
            const anyCheckbox = document.getElementById(`chk-any-${key}`);
            const normalCheckboxes = document.querySelectorAll(
                `.checkbox-${key === 'local_areas' ? 'local_area' : key.slice(0, -1)}`);

            if (anyCheckbox.checked) {
                filterState[key].temp = ['any'];
                normalCheckboxes.forEach(cb => {
                    cb.checked = true;
                    cb.disabled = true;
                });
            } else {
                filterState[key].temp = [];
                normalCheckboxes.forEach(cb => {
                    cb.checked = false;
                    cb.disabled = false;
                });
            }
        }

        // Handle change on individual checkboxes
        function handleIndividualChange(key) {
            const normalCheckboxes = document.querySelectorAll(
                `.checkbox-${key === 'local_areas' ? 'local_area' : key.slice(0, -1)}`);
            const checkedValues = [];

            normalCheckboxes.forEach(cb => {
                if (cb.checked) {
                    checkedValues.push(cb.value);
                }
            });

            // Update temp state
            filterState[key].temp = checkedValues;

            // If nothing is checked, fallback to 'any' checked state
            if (checkedValues.length === 0) {
                const anyCheckbox = document.getElementById(`chk-any-${key}`);
                anyCheckbox.checked = true;
                toggleAnySelectAll(key);
            }
        }

        // Confirm selection from modal
        function confirmSelections(key) {
            const anyCheckbox = document.getElementById(`chk-any-${key}`);
            if (anyCheckbox.checked) {
                filterState[key].confirmed = ['any'];
            } else {
                filterState[key].confirmed = [...filterState[key].temp];
            }

            updateDisplaySummary(key);
            updateFilterTags(key);
            closeModal(key);
        }

        // Clear all checked options
        function clearAllOptions(key) {
            const anyCheckbox = document.getElementById(`chk-any-${key}`);
            anyCheckbox.checked = true;
            toggleAnySelectAll(key);
        }

        // Update display text of filter dropdown triggers
        function updateDisplaySummary(key) {
            const summarySpan = document.getElementById(`${key}-summary`);
            if (!summarySpan) return;

            const confirmed = filterState[key].confirmed;

            if (confirmed.includes('any')) {
                let defaultText = 'Any';
                if (key === 'colleges') defaultText = 'Any Colleges';
                if (key === 'quotas') defaultText = 'Any Quotas';
                if (key === 'rounds') defaultText = 'Over All';
                if (key === 'local_areas') defaultText = 'All Over India';
                summarySpan.innerText = defaultText;
                return;
            }

            if (key === 'rounds') {
                // For rounds, display names rather than IDs
                const roundNames = [];
                confirmed.forEach(id => {
                    const cb = document.querySelector(`.checkbox-round[value="${id}"]`);
                    if (cb) {
                        roundNames.push(cb.getAttribute('data-name'));
                    }
                });
                summarySpan.innerText = roundNames.length > 2 ?
                    `${roundNames.length} Rounds` :
                    roundNames.join(', ');
                return;
            }

            // Standard display join or count
            summarySpan.innerText = confirmed.length > 1 ?
                `${confirmed.length} Selected` :
                confirmed[0];
        }

        // Update tags below the filter trigger (specifically for Quotas and Local Areas as shown in screenshots)
        function updateFilterTags(key) {
            // Only generate tags for quotas and local_areas
            if (key !== 'quotas' && key !== 'local_areas') return;

            const container = document.getElementById(`${key}-tags-container`);
            if (!container) return;

            container.innerHTML = '';
            const confirmed = filterState[key].confirmed;

            // Text values to output
            let tagValues = [];
            if (confirmed.includes('any')) {
                tagValues = [key === 'quotas' ? 'Any Quotas' : 'All Over India'];
            } else {
                tagValues = [...confirmed];
            }

            tagValues.forEach(value => {
                const tag = document.createElement('div');
                tag.className =
                    'inline-flex items-center gap-1.5 bg-rose-50 border border-rose-100 text-rose-600 rounded-lg px-3 py-1.5 text-xs font-semibold';
                tag.innerHTML = `
                    <span>${value}</span>
                    <button type="button" onclick="removeTag('${key}', '${value}')" class="hover:bg-rose-100 hover:text-rose-800 transition-colors p-0.5 rounded">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                `;
                container.appendChild(tag);
            });
        }

        // Tag removal callback
        function removeTag(key, value) {
            let fallbackValue = 'any';
            if (key === 'quotas' && value === 'Any Quotas') return;
            if (key === 'local_areas' && value === 'All Over India') return;

            const index = filterState[key].confirmed.indexOf(value);
            if (index > -1) {
                filterState[key].confirmed.splice(index, 1);
            }

            if (filterState[key].confirmed.length === 0) {
                filterState[key].confirmed = ['any'];
            }

            updateDisplaySummary(key);
            updateFilterTags(key);
        }

        // Search Input filter logic for modal lists
        function setupModalSearch(key, itemClass) {
            const searchInput = document.getElementById(`search-${key}`);
            if (!searchInput) return;

            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim().toLowerCase();
                const items = document.querySelectorAll(`.${itemClass}`);

                items.forEach(item => {
                    const name = item.getAttribute('data-name');
                    if (name.includes(query)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        // Initialize searches
        setupModalSearch('colleges', 'item-college');
        setupModalSearch('quotas', 'item-quota');
        setupModalSearch('rounds', 'item-round');
        setupModalSearch('local_areas', 'item-local_area');


        // ==============================================
        // TUITION FEE RANGE SLIDER INTERACTION
        // ==============================================
        const feeSlider = document.getElementById('fee-slider');
        const feeSelectedText = document.getElementById('fee-selected-text');

        feeSlider.addEventListener('input', function(e) {
            const val = parseInt(e.target.value);
            feeSelectedText.innerText = `Selected: ₹${val.toLocaleString('en-IN')}`;
        });


        // ==============================================
        // YAJRA DATATABLES INTEGRATION
        // ==============================================
        let analysisTable = null;
        // Container for custom column visibility checkboxes (vertical list)
        const colVisContainer = document.createElement('div');
        colVisContainer.id = 'column-visibility-list';
        colVisContainer.style.marginBottom = '10px';
        colVisContainer.style.display = 'flex';
        colVisContainer.style.flexWrap = 'wrap';
        colVisContainer.style.gap = '10px';
        colVisContainer.style.marginBottom = '20px';
// Insert container before the table element
const tableEl = document.getElementById('analysis-table');
if (tableEl && tableEl.parentNode) {
    tableEl.parentNode.insertBefore(colVisContainer, tableEl);
}

        $(document).ready(function() {
            // Initial tags and state
            updateDisplaySummary('colleges');
            updateDisplaySummary('quotas');
            updateDisplaySummary('rounds');
            updateDisplaySummary('local_areas');

            updateFilterTags('quotas');
            updateFilterTags('local_areas');

            // Set up DataTable initialization
analysisTable = $('#analysis-table').DataTable({
                // Removed default Buttons collection for column visibility
                processing: true,
                serverSide: true,
                searching: true,
                deferRender: true,
                order: [
                    [6, 'desc']
                ], // Order by Gen Closing Rank by default
                ajax: {
                    url: "{{ route('home') }}",
                    data: function(d) {
                        d.rank = $('#rank-input').val();
                        d.colleges = filterState.colleges.confirmed;
                        d.quotas = filterState.quotas.confirmed;
                        d.rounds = filterState.rounds.confirmed;
                        d.local_areas = filterState.local_areas.confirmed;
                        d.fee_min = 0;
                        d.fee_max = $('#fee-slider').val();
                    }
                },
                columns: [{
                        data: 'state_name',
                        name: 'state_name'
                    },
                    {
                        data: 'college_name',
                        name: 'college_name',
                        render: function(data) {
                            return `<div class="font-medium text-slate-800">${data}</div>`;
                        }
                    },
                    {
                        data: 'category',
                        name: 'category',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'round_id',
                        name: 'round_id',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'local_area',
                        name: 'local_area',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'total_seats',
                        name: 'total_seats',
                        className: 'text-center'
                    },
                    {
                        data: 'gen_closing_rank',
                        name: 'gen_closing_rank',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString() : '-';
                        }
                    },
                    {
                        data: 'gen_closing_mark',
                        name: 'gen_closing_mark',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '-';
                        }
                    },
                    {
                        data: 'fem_closing_rank',
                        name: 'fem_closing_rank',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString() : '-';
                        }
                    },
                    {
                        data: 'fem_closing_mark',
                        name: 'fem_closing_mark',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '-';
                        }
                    },
                    {
                        data: 'tuition_fee',
                        name: 'tuition_fee',
                        render: function(data) {
                            return data > 0 ? '₹' + parseInt(data).toLocaleString('en-IN') : '₹0';
                        }
                    }
                ],
                drawCallback: function(settings) {
                    // Update rows display count
                    const api = this.api();
                    const info = api.page.info();
                    $('#results-count').text(info.recordsDisplay);
                    $('#total-seats-count').text('(' + info.recordsDisplay + ')');
                }
            });
            initColumnVisibility();

            // Get Analysis Button Click
            $('#btn-get-analysis').on('click', function() {
                // Update results summary text
                updateResultsSummaryLabel();

                // Show panel
                $('#results-panel').removeClass('hidden');

                // Reload Yajra DataTable with current parameters
                analysisTable.ajax.reload();
            });
        });
        // Define function to create vertical column visibility checkboxes
        function initColumnVisibility() {
    if (!analysisTable) return;

    const container = document.getElementById('column-visibility-list');
    if (!container) return;

    container.innerHTML = '';

    analysisTable.columns().every(function(idx) {
        const col = this;
        const title = $(col.header()).text().trim() || `Column ${idx + 1}`;

        const wrapper = document.createElement('div');
        wrapper.className = 'colvis-item';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `colvis-${idx}`;
        checkbox.checked = col.visible();
        checkbox.className = 'colvis-checkbox';

        checkbox.addEventListener('change', function() {
            col.visible(this.checked);

            if (this.checked) {
                wrapper.classList.add('active');
            } else {
                wrapper.classList.remove('active');
            }
        });

        const label = document.createElement('label');
        label.setAttribute('for', `colvis-${idx}`);
        label.className = 'colvis-button';
        label.innerHTML = `
            <span class="checkmark">✓</span>
            <span>${title}</span>
        `;

        if (checkbox.checked) {
            wrapper.classList.add('active');
        }

        wrapper.appendChild(checkbox);
        wrapper.appendChild(label);

        container.appendChild(wrapper);
    });
}

        // Update the textual description of applied filters above result table
        function updateResultsSummaryLabel() {
            const summaryText = document.getElementById('results-summary-text');
            if (!summaryText) return;

            // Colleges
            let collegesPart = 'Any Colleges';
            const collegesVal = filterState.colleges.confirmed;
            if (!collegesVal.includes('any')) {
                collegesPart = collegesVal.length > 2 ?
                    `${collegesVal.length} Colleges` :
                    collegesVal.join(', ');
            }

            // Quotas
            let quotasPart = 'Any Quotas';
            const quotasVal = filterState.quotas.confirmed;
            if (!quotasVal.includes('any')) {
                quotasPart = quotasVal.join(', ');
            }

            // Rounds
            let roundsPart = 'Over All';
            const roundsVal = filterState.rounds.confirmed;
            if (!roundsVal.includes('any')) {
                const roundNames = [];
                roundsVal.forEach(id => {
                    const cb = document.querySelector(`.checkbox-round[value="${id}"]`);
                    if (cb) roundNames.push(cb.getAttribute('data-name'));
                });
                roundsPart = roundNames.join(', ');
            }

            // Local Areas
            let localPart = 'All Over India';
            const localVal = filterState.local_areas.confirmed;
            if (!localVal.includes('any')) {
                localPart = localVal.join(', ');
            }

            summaryText.innerText = `[Rounds: ${roundsPart}, Categories: ${quotasPart}, Local Areas: ${localPart}]`;
        }
    </script>
</body>

</html>
