@extends('backend.layouts.app', ['title' => 'Stock Management'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        div.dataTables_wrapper div.dataTables_length {
            float: left;
            margin-left: 18px !important;
            padding-left: 2px;
            padding-right: 2px;
        }

        div.dataTables_wrapper div.dataTables_length label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #475569;
        }

        div.dataTables_wrapper div.dataTables_length select {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 4px 26px 4px 10px !important;
            margin: 0 4px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #334155 !important;
            background-color: #ffffff !important;
        }

        /* Clean Pagination */
        div.dataTables_wrapper div.dataTables_paginate .paginate_button,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button:active {
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
            color: #475569 !important;
        }

        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current {
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover {
            color: #4f46e5 !important;
        }



        @media print {

            table.dataTable,
            table.dataTable * {
                border-collapse: collapse !important;
            }

            table.dataTable {
                border-collapse: collapse !important;
                width: 100% !important;
                border: 1px solid #000000 !important;
            }

            table.dataTable th,
            table.dataTable td,
            table.dataTable thead th,
            table.dataTable thead td,
            table.dataTable tbody td,
            table.dataTable tbody tr:first-child td {
                border: 1px solid #000000 !important;
                border-top: 1px solid #000000 !important;
                border-bottom: 1px solid #000000 !important;
                border-left: 1px solid #000000 !important;
                border-right: 1px solid #000000 !important;
                padding: 6px 8px !important;
                color: #000000 !important;
            }

            table.dataTable thead th {
                background-color: #f1f5f9 !important;
                font-weight: 700 !important;
                color: #000000 !important;
                border: 1px solid #000000 !important;
            }

            .dt-buttons,
            .dataTables_filter,
            .dataTables_length,
            .dataTables_paginate,
            .dataTables_info,
            button,
            .no-print {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6" x-data="{ activeTab: 'balances', filterStatus: '' }">

        <!-- Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-500">Inventory</span>
                    <span>/</span>
                    <span class="text-slate-700 font-semibold">Stock Control</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Stock Management Hub</h1>
                <p class="text-xs text-slate-500 mt-0.5">Real-time inventory levels, stock-in replenishment, outbound
                    dispatches, and audit movements ledger.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('backend.stock-ins.index') }}"
                    class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <i class="bi bi-box-arrow-in-down-left"></i>
                    <span>Stock In</span>
                </a>

                <a href="{{ route('backend.stock-outs.index') }}"
                    class="px-3.5 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Stock Out</span>
                </a>

                <a href="{{ route('backend.stock-adjustments.index') }}"
                    class="px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <i class="bi bi-sliders2"></i>
                    <span>Adjustments</span>
                </a>
            </div>
        </div>

        <!-- Alert notifications -->
        @if (session('success'))
            <div
                class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Metric KPI Cards: One by One Simple Numbers -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- 1. Available Stock -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-boxes"></i>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-slate-500 leading-tight">Available Stock</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">
                        {{ number_format($stats['total_units']) }}
                    </div>
                </div>
            </div>

            <!-- 2. Total Stock In -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-box-arrow-in-down-left"></i>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-slate-500 leading-tight">Total Stock In</div>
                    <div class="text-xl font-bold text-emerald-700 mt-0.5 font-mono">
                        +{{ number_format($stats['total_stock_in_qty']) }}
                    </div>
                </div>
            </div>

            <!-- 3. Total Stock Out -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-box-arrow-up-right"></i>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-slate-500 leading-tight">Total Stock Out</div>
                    <div class="text-xl font-bold text-rose-700 mt-0.5 font-mono">
                        -{{ number_format($stats['total_stock_out_qty']) }}
                    </div>
                </div>
            </div>

            <!-- 4. Low Stock -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-slate-500 leading-tight">Low Stock</div>
                    <div class="text-xl font-bold text-amber-700 mt-0.5">
                        {{ $stats['low_stock_count'] }}
                    </div>
                </div>
            </div>

            <!-- 5. Out of Stock -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-slate-500 leading-tight">Out of Stock</div>
                    <div class="text-xl font-bold text-rose-700 mt-0.5">
                        {{ $stats['out_of_stock_count'] }}
                    </div>
                </div>
            </div>

            <!-- 6. Total Valuation -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-slate-500 leading-tight">Total Valuation</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5 font-mono">
                        ${{ number_format($stats['total_cost_valuation'], 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs Bar & DataTable Container (No background color) -->
        <div class="space-y-4">
            <div class="border-b border-slate-200 pb-1 flex flex-wrap items-center justify-between gap-4">
                <!-- Tabs -->
                <div class="flex items-center gap-1 -mb-px">
                    <button type="button" @click="activeTab = 'balances'"
                        :class="activeTab === 'balances' ? 'border-indigo-600 text-indigo-600 font-bold' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="px-4 py-2.5 text-xs border-b-2 font-medium transition flex items-center gap-2">
                        <i class="bi bi-stack"></i>
                        <span>Stock Balances</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 text-slate-700 font-mono">{{ $stats['total_variations'] }}</span>
                    </button>

                    <button type="button" @click="activeTab = 'ledger'"
                        :class="activeTab === 'ledger' ? 'border-indigo-600 text-indigo-600 font-bold' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="px-4 py-2.5 text-xs border-b-2 font-medium transition flex items-center gap-2">
                        <i class="bi bi-clock-history"></i>
                        <span>Audit Movements Ledger</span>
                    </button>

                    <button type="button" @click="activeTab = 'stock_in'"
                        :class="activeTab === 'stock_in' ? 'border-indigo-600 text-indigo-600 font-bold' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="px-4 py-2.5 text-xs border-b-2 font-medium transition flex items-center gap-2">
                        <i class="bi bi-box-arrow-in-down-left text-emerald-600"></i>
                        <span>Recent Stock In</span>
                    </button>

                    <button type="button" @click="activeTab = 'stock_out'"
                        :class="activeTab === 'stock_out' ? 'border-indigo-600 text-indigo-600 font-bold' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="px-4 py-2.5 text-xs border-b-2 font-medium transition flex items-center gap-2">
                        <i class="bi bi-box-arrow-up-right text-rose-600"></i>
                        <span>Recent Stock Out</span>
                    </button>

                    <button type="button" @click="activeTab = 'adjustments'"
                        :class="activeTab === 'adjustments' ? 'border-indigo-600 text-indigo-600 font-bold' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="px-4 py-2.5 text-xs border-b-2 font-medium transition flex items-center gap-2">
                        <i class="bi bi-sliders2 text-indigo-600"></i>
                        <span>Recent Adjustments</span>
                    </button>
                </div>

                <!-- Stock Balances Filter Buttons (Only visible on Balances tab) -->
                <div x-show="activeTab === 'balances'" class="flex items-center gap-1.5 pb-2 text-xs">
                    <span class="text-slate-400 text-[11px] font-medium mr-1">Filter:</span>
                    <button type="button" onclick="applyStatusFilter('')"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                        All ({{ $stats['total_variations'] }})
                    </button>
                    <button type="button" onclick="applyStatusFilter('in_stock')"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                        In Stock ({{ $stats['in_stock_count'] }})
                    </button>
                    <button type="button" onclick="applyStatusFilter('low_stock')"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition">
                        Low Stock ({{ $stats['low_stock_count'] }})
                    </button>
                    <button type="button" onclick="applyStatusFilter('out_of_stock')"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">
                        Out of Stock ({{ $stats['out_of_stock_count'] }})
                    </button>
                </div>
            </div>

            <!-- TAB 1: Master Stock Balances Table -->
            <div x-show="activeTab === 'balances'" class="w-full">
                {!! $dataTable->table(['class' => 'display', 'style' => 'width:100%']) !!}
            </div>

            <!-- TAB 2: Live Audit Movements Ledger -->
            <div x-show="activeTab === 'ledger'" class="space-y-4" style="display: none;">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-2.5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="text-xs text-slate-500">
                            Chronological timeline of all stock transactions (Inbound, Outbound, Audits & Corrections).
                        </div>
                        <span id="movementsCountBadge"
                            class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                            Showing {{ count($recentMovements) }} entries
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="resetMovementsFilterBtn" onclick="resetMovementFilters()"
                            class="text-xs font-semibold px-2.5 py-1 rounded-lg text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition items-center gap-1.5"
                            style="display: none;">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Reset Filters</span>
                            <span id="activeFiltersCountBadge" class="px-1.5 py-0.2 rounded-full bg-rose-600 text-white text-[10px] font-bold">0</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto my-2">
                    <table class="w-full text-left text-xs border-collapse border border-black bg-white table-black" id="movementsMasterTable">
                        <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[11px]">
                            <tr>
                                <th class="border border-black px-3 py-2.5">Date / Time</th>
                                <th class="border border-black px-3 py-2.5">Product</th>
                                <th class="border border-black px-3 py-2.5">Variation</th>
                                <th class="border border-black px-3 py-2.5 text-center">Movement Type</th>
                                <th class="border border-black px-3 py-2.5 text-center">Qty Change</th>
                                <th class="border border-black px-3 py-2.5 text-center">Before &rarr; After</th>
                                <th class="border border-black px-3 py-2.5 text-right">Cost</th>
                                <th class="border border-black px-3 py-2.5">Reference</th>
                                <th class="border border-black px-3 py-2.5">Reason</th>
                                <th class="border border-black px-3 py-2.5">Logged By</th>
                            </tr>
                            <tr class="bg-slate-50">
                                <th class="border border-black p-1 font-normal">
                                    <select id="colFilter_date" onchange="filterMovementsTable()" title="Filter Date"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Dates</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal">
                                    <select id="colFilter_product" onchange="filterMovementsTable()" title="Filter Product"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Products</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal">
                                    <select id="colFilter_variation" onchange="filterMovementsTable()" title="Filter Variation"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Variations</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal text-center">
                                    <select id="colFilter_type" onchange="filterMovementsTable()" title="Filter Movement Type"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Movement Types</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal text-center">
                                    <select id="colFilter_qty" onchange="filterMovementsTable()" title="Filter Quantity Change"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Changes</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal text-center">
                                    <select id="colFilter_flow" onchange="filterMovementsTable()" title="Filter Balance Flow"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Balances</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal text-right">
                                    <select id="colFilter_cost" onchange="filterMovementsTable()" title="Filter Cost"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Costs</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal">
                                    <select id="colFilter_reference" onchange="filterMovementsTable()" title="Filter Reference"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All References</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal">
                                    <select id="colFilter_reason" onchange="filterMovementsTable()" title="Filter Reason"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Reasons</option>
                                    </select>
                                </th>
                                <th class="border border-black p-1 font-normal">
                                    <select id="colFilter_logged_by" onchange="filterMovementsTable()" title="Filter Logged By"
                                        class="movement-col-filter w-full text-[11px] bg-white border border-slate-300 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:border-indigo-600 font-normal">
                                        <option value="">All Logged By</option>
                                    </select>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($recentMovements as $mv)
                                <tr class="hover:bg-slate-50 transition-colors movement-row"
                                    data-date="{{ $mv->created_at ? $mv->created_at->format('Y-m-d') : '' }}"
                                    data-datetime="{{ $mv->created_at ? $mv->created_at->format('Y-m-d H:i') : '' }}"
                                    data-product="{{ optional($mv->product)->name ?? '-' }}"
                                    data-variation="{{ (optional(optional($mv->variation)->color)->name ?? 'Standard') . ' / ' . (optional(optional($mv->variation)->size)->name ?? 'Standard') }}"
                                    data-type="{{ $mv->type }}"
                                    data-type-label="{{ str_replace('_', ' ', $mv->type) }}"
                                    data-qty="{{ $mv->quantity > 0 ? '+' . $mv->quantity . ' pcs' : $mv->quantity . ' pcs' }}"
                                    data-qty-sign="{{ $mv->quantity > 0 ? 'positive' : ($mv->quantity < 0 ? 'negative' : 'zero') }}"
                                    data-flow="{{ $mv->stock_before . ' → ' . $mv->stock_after }}"
                                    data-cost="${{ number_format((float) $mv->unit_cost, 2) }}"
                                    data-reference="{{ $mv->reference_no }}"
                                    data-reason="{{ $mv->reason_label }}"
                                    data-logged-by="{{ optional($mv->creator)->name ?? 'System' }}">
                                    <td class="border border-black px-3 py-2 font-mono text-slate-700 whitespace-nowrap">{{ $mv->created_at ? $mv->created_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td class="border border-black px-3 py-2 font-semibold text-slate-900">{{ optional($mv->product)->name ?? '-' }}</td>
                                    <td class="border border-black px-3 py-2 text-slate-700 whitespace-nowrap">
                                        {{ optional(optional($mv->variation)->color)->name ?? 'Standard' }} / {{ optional(optional($mv->variation)->size)->name ?? 'Standard' }}
                                    </td>
                                    <td class="border border-black px-3 py-2 text-center whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ match($mv->type) {
                                             'stock_in' => ' text-emerald-800',
                                             'stock_out' => ' text-rose-800',
                                             'adjustment' => ' text-indigo-800',
                                             'sale' => ' text-blue-800',
                                             'return' => ' text-amber-800',
                                             default => ' text-slate-800',
                                         } }}">
                                            {{ str_replace('_', ' ', $mv->type) }}
                                        </span>
                                    </td>
                                    <td class="border border-black px-3 py-2 text-center font-mono font-bold whitespace-nowrap {{ $mv->quantity > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $mv->quantity > 0 ? '+' . $mv->quantity : $mv->quantity }} pcs
                                    </td>
                                    <td class="border border-black px-3 py-2 text-center font-mono text-slate-700 whitespace-nowrap">
                                        {{ $mv->stock_before }} &rarr; <strong class="text-slate-900">{{ $mv->stock_after }}</strong>
                                    </td>
                                    <td class="border border-black px-3 py-2 text-right font-mono text-slate-800 whitespace-nowrap">
                                        ${{ number_format((float) $mv->unit_cost, 2) }}
                                    </td>
                                    <td class="border border-black px-3 py-2 font-mono font-bold whitespace-nowrap text-indigo-700">
                                        @if ($mv->reference_url && $mv->reference_no !== '-')
                                            <a href="{{ $mv->reference_url }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">{{ $mv->reference_no }}</a>
                                        @else
                                            {{ $mv->reference_no }}
                                        @endif
                                    </td>
                                    <td class="border border-black px-3 py-2 text-slate-800">
                                        {{ $mv->reason_label }}
                                    </td>
                                    <td class="border border-black px-3 py-2 text-slate-700 whitespace-nowrap">{{ optional($mv->creator)->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="border border-black p-6 text-center text-slate-400">No stock movement entries recorded yet.</td>
                                </tr>
                            @endforelse
                            <tr id="noMatchingMovementsRow" style="display: none;">
                                <td colspan="10" class="border border-black p-6 text-center text-slate-400">
                                    No stock movement records match the selected filters.
                                    <button type="button" onclick="resetMovementFilters()" class="text-indigo-600 underline font-semibold ml-2 hover:text-indigo-800">Reset filters</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: Recent Stock In Batches -->
            <div x-show="activeTab === 'stock_in'" class="space-y-4" style="display: none;">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs text-slate-500">Most recent inbound replenishment shipments</span>
                    <a href="{{ route('backend.stock-ins.index') }}"
                        class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                        <span>Manage all Stock In</span> &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto my-2">
                    <table class="w-full text-left text-xs border-collapse border border-black bg-white table-black">
                        <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[11px]">
                            <tr>
                                <th class="border border-black px-3 py-2.5">Ref No</th>
                                <th class="border border-black px-3 py-2.5">Date</th>
                                <th class="border border-black px-3 py-2.5">Supplier</th>
                                <th class="border border-black px-3 py-2.5 text-center">Items</th>
                                <th class="border border-black px-3 py-2.5 text-center">Quantity</th>
                                <th class="border border-black px-3 py-2.5 text-right">Total Cost</th>
                                <th class="border border-black px-3 py-2.5 text-center">Status</th>
                                <th class="border border-black px-3 py-2.5 text-right pr-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($recentStockIns as $si)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="border border-black px-3 py-2 font-mono font-bold text-indigo-700 whitespace-nowrap">{{ $si->reference_no }}</td>
                                    <td class="border border-black px-3 py-2 text-slate-700 whitespace-nowrap">{{ $si->received_date ? $si->received_date->format('Y-m-d') : '-' }}</td>
                                    <td class="border border-black px-3 py-2 text-slate-800 font-medium">{{ optional($si->supplier)->name ?? 'General Supplier' }}</td>
                                    <td class="border border-black px-3 py-2 text-center text-slate-700 whitespace-nowrap">{{ $si->items->count() }} items</td>
                                    <td class="border border-black px-3 py-2 text-center font-mono font-bold text-emerald-700 whitespace-nowrap">+{{ number_format($si->total_quantity) }} pcs</td>
                                    <td class="border border-black px-3 py-2 text-right font-mono font-bold text-slate-900 whitespace-nowrap">${{ number_format($si->total_cost, 2) }}</td>
                                    <td class="border border-black px-3 py-2 text-center whitespace-nowrap">
                                        <span class="font-bold uppercase text-[10px] {{ $si->status === 'received' ? 'text-emerald-800' : 'text-slate-600' }}">{{ $si->status }}</span>
                                    </td>
                                    <td class="border border-black px-3 py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('backend.stock-ins.show', $si->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">View Details &rarr;</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="border border-black p-6 text-center text-slate-400">No stock in records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: Recent Stock Out Dispatches -->
            <div x-show="activeTab === 'stock_out'" class="space-y-4" style="display: none;">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs text-slate-500">Most recent outbound material issues & dispatches</span>
                    <a href="{{ route('backend.stock-outs.index') }}"
                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold flex items-center gap-1">
                        <span>Manage all Stock Out</span> &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto my-2">
                    <table class="w-full text-left text-xs border-collapse border border-black bg-white table-black">
                        <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[11px]">
                            <tr>
                                <th class="border border-black px-3 py-2.5">Ref No</th>
                                <th class="border border-black px-3 py-2.5">Date</th>
                                <th class="border border-black px-3 py-2.5">Reason</th>
                                <th class="border border-black px-3 py-2.5">Recipient</th>
                                <th class="border border-black px-3 py-2.5 text-center">Quantity</th>
                                <th class="border border-black px-3 py-2.5 text-right">Value</th>
                                <th class="border border-black px-3 py-2.5 text-center">Status</th>
                                <th class="border border-black px-3 py-2.5 text-right pr-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($recentStockOuts as $so)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="border border-black px-3 py-2 font-mono font-bold text-rose-700 whitespace-nowrap">{{ $so->reference_no }}</td>
                                    <td class="border border-black px-3 py-2 text-slate-700 whitespace-nowrap">{{ $so->date ? $so->date->format('Y-m-d') : '-' }}</td>
                                    <td class="border border-black px-3 py-2 text-slate-800 font-medium">{{ $so->reason_label }}</td>
                                    <td class="border border-black px-3 py-2 text-slate-700">{{ $so->recipient_name ?: '-' }}</td>
                                    <td class="border border-black px-3 py-2 text-center font-mono font-bold text-rose-700 whitespace-nowrap">-{{ number_format($so->total_quantity) }} pcs</td>
                                    <td class="border border-black px-3 py-2 text-right font-mono font-bold text-slate-900 whitespace-nowrap">${{ number_format($so->total_cost, 2) }}</td>
                                    <td class="border border-black px-3 py-2 text-center whitespace-nowrap">
                                        <span class="font-bold uppercase text-[10px] {{ $so->status === 'dispatched' ? 'text-emerald-800' : 'text-slate-600' }}">{{ $so->status }}</span>
                                    </td>
                                    <td class="border border-black px-3 py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('backend.stock-outs.show', $so->id) }}" class="text-rose-600 hover:text-rose-800 font-medium text-xs">View Slip &rarr;</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="border border-black p-6 text-center text-slate-400">No stock out dispatches found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: Recent Adjustments -->
            <div x-show="activeTab === 'adjustments'" class="space-y-4" style="display: none;">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs text-slate-500">Most recent inventory reconciliations & audit corrections</span>
                    <a href="{{ route('backend.stock-adjustments.index') }}"
                        class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                        <span>Manage all Adjustments</span> &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto my-2">
                    <table class="w-full text-left text-xs border-collapse border border-black bg-white table-black">
                        <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[11px]">
                            <tr>
                                <th class="border border-black px-3 py-2.5">Ref No</th>
                                <th class="border border-black px-3 py-2.5">Date</th>
                                <th class="border border-black px-3 py-2.5">Reason</th>
                                <th class="border border-black px-3 py-2.5 text-center">Items</th>
                                <th class="border border-black px-3 py-2.5 text-center">Net Qty Change</th>
                                <th class="border border-black px-3 py-2.5 text-right">Cost Impact</th>
                                <th class="border border-black px-3 py-2.5">Logged By</th>
                                <th class="border border-black px-3 py-2.5 text-right pr-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($recentAdjustments as $adj)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="border border-black px-3 py-2 font-mono font-bold text-indigo-700 whitespace-nowrap">{{ $adj->reference_no }}</td>
                                    <td class="border border-black px-3 py-2 text-slate-700 whitespace-nowrap">{{ $adj->date ? $adj->date->format('Y-m-d') : '-' }}</td>
                                    <td class="border border-black px-3 py-2 text-slate-800 font-medium">{{ $adj->reason_label }}</td>
                                    <td class="border border-black px-3 py-2 text-center text-slate-700 whitespace-nowrap">{{ $adj->total_items }} items</td>
                                    <td class="border border-black px-3 py-2 text-center font-mono font-bold whitespace-nowrap {{ $adj->total_qty_adjusted >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $adj->total_qty_adjusted >= 0 ? '+' . $adj->total_qty_adjusted : $adj->total_qty_adjusted }} pcs
                                    </td>
                                    <td class="border border-black px-3 py-2 text-right font-mono font-bold whitespace-nowrap {{ $adj->total_cost_impact >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $adj->total_cost_impact >= 0 ? '+$' : '-$' }}{{ number_format(abs($adj->total_cost_impact), 2) }}
                                    </td>
                                    <td class="border border-black px-3 py-2 text-slate-700 whitespace-nowrap">{{ optional($adj->creator)->name ?? 'Admin' }}</td>
                                    <td class="border border-black px-3 py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('backend.stock-adjustments.show', $adj->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">View Details &rarr;</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="border border-black p-6 text-center text-slate-400">No stock adjustment records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal: Movement Timeline Audit Slide-over / Modal -->
    <div id="variationHistoryModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-3 py-6 text-center sm:p-6">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                onclick="closeVariationHistoryModal()"></div>

            <div
                class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-6 w-full max-w-6xl xl:max-w-7xl border border-slate-200 z-10">
                <div class="p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 leading-6" id="vhProductName">Product
                                    Movement Ledger</h3>
                                <p class="text-xs text-slate-500 font-mono" id="vhProductSku">Item audit trail and
                                    historical balance changes</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeVariationHistoryModal()"
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <!-- Info banner -->
                    <div
                        class="grid grid-cols-2 sm:grid-cols-4 gap-4 my-4 p-4 bg-slate-50 rounded-xl text-xs border border-slate-200">
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Attributes</span>
                            <span class="font-bold text-slate-800" id="vhVariation">Standard</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Current Stock</span>
                            <span class="font-mono font-bold text-emerald-700 text-sm" id="vhCurrentStock">0 pcs</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Cost Price</span>
                            <span class="font-mono font-bold text-slate-800" id="vhCost">$0.00</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Category</span>
                            <span class="font-semibold text-slate-800" id="vhCategory">-</span>
                        </div>
                    </div>

                    <!-- Movement List -->
                    <div class="border border-black rounded-lg overflow-hidden max-h-[520px] overflow-y-auto my-2">
                        <table class="w-full text-left text-xs border-collapse border border-black bg-white table-black">
                            <thead
                                class="bg-slate-100 text-slate-800 font-bold uppercase text-[11px] sticky top-0">
                                <tr>
                                    <th class="border border-black px-3 py-2.5">Date</th>
                                    <th class="border border-black px-3 py-2.5 text-center">Type</th>
                                    <th class="border border-black px-3 py-2.5 text-center">Change</th>
                                    <th class="border border-black px-3 py-2.5 text-center">Balance Flow</th>
                                    <th class="border border-black px-3 py-2.5">Reference</th>
                                    <th class="border border-black px-3 py-2.5">Reason</th>
                                    <th class="border border-black px-3 py-2.5 pr-4">By</th>
                                </tr>
                            </thead>
                            <tbody id="vhMovementsBody" class="bg-white">
                                <!-- Injected via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3.5 border-t border-slate-100 flex items-center justify-end">
                    <button type="button" onclick="closeVariationHistoryModal()"
                        class="px-5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 bg-slate-100 rounded-lg transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}

    <script>
        // Filter DataTables by Stock Status
        function applyStatusFilter(status) {
            const table = $('#stock-inventory-table').DataTable();
            table.ajax.url('{{ route('backend.stocks.index') }}?stock_status=' + encodeURIComponent(status)).load();
        }

        // Quick action redirection or triggers
        function quickStockIn(productId, variationId) {
            window.location.href = "{{ route('backend.stock-ins.index') }}";
        }

        function quickStockOut(productId, variationId, currentStock, cost) {
            window.location.href = "{{ route('backend.stock-outs.index') }}";
        }

        function quickAdjust(productId, variationId, currentStock) {
            window.location.href = "{{ route('backend.stock-adjustments.index') }}";
        }

        // View single variation movement history modal
        function viewVariationHistory(variationId) {
            const url = "{{ url('backend/stocks') }}/" + variationId + "/history";

            document.getElementById('vhMovementsBody').innerHTML =
                '<tr><td colspan="7" class="p-6 text-center text-slate-400">Loading movement ledger...</td></tr>';
            document.getElementById('variationHistoryModal').classList.remove('hidden');

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const p = data.product;
                        document.getElementById('vhProductName').innerText = p.name;
                        document.getElementById('vhProductSku').innerText = 'SKU: ' + p.sku;
                        document.getElementById('vhVariation').innerText = p.color + ' / ' + p.size;
                        document.getElementById('vhCurrentStock').innerText = p.stock + ' pcs';
                        document.getElementById('vhCost').innerText = '$' + p.cost;
                        document.getElementById('vhCategory').innerText = p.category + ' (' + p.brand + ')';

                        const tbody = document.getElementById('vhMovementsBody');
                        tbody.innerHTML = '';

                        if (data.movements.length === 0) {
                            tbody.innerHTML =
                                '<tr><td colspan="7" class="border border-black p-6 text-center text-slate-400">No movement history for this item.</td></tr>';
                            return;
                        }

                        data.movements.forEach(m => {
                            const isPositive = m.quantity > 0;
                            const qtyDisplay = isPositive ? `+${m.quantity}` : m.quantity;
                            const qtyClass = isPositive ? 'text-emerald-700' : 'text-rose-700';

                            const refHtml = (m.reference_url && m.reference_no && m.reference_no !== '-')
                                ? `<a href="${m.reference_url}" class="text-indigo-600 hover:text-indigo-900 hover:underline font-mono font-bold">${m.reference_no}</a>`
                                : `<span class="font-mono font-bold text-indigo-700">${m.reference_no || '-'}</span>`;

                            tbody.innerHTML += `
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="border border-black px-3 py-2 font-mono text-slate-700 whitespace-nowrap">${m.date}</td>
                                    <td class="border border-black px-3 py-2 text-center whitespace-nowrap">
                                        <span class="font-bold uppercase text-[10px] text-slate-800">
                                            ${m.type.replace('_', ' ')}
                                        </span>
                                    </td>
                                    <td class="border border-black px-3 py-2 text-center font-mono font-bold ${qtyClass} whitespace-nowrap">
                                        ${qtyDisplay} pcs
                                    </td>
                                    <td class="border border-black px-3 py-2 text-center font-mono text-slate-700 whitespace-nowrap">
                                        ${m.stock_before} &rarr; <strong class="text-slate-900">${m.stock_after}</strong>
                                    </td>
                                    <td class="border border-black px-3 py-2 font-mono whitespace-nowrap">
                                        ${refHtml}
                                    </td>
                                    <td class="border border-black px-3 py-2 text-slate-800 text-xs">
                                        ${m.reason_label || '-'}
                                    </td>
                                    <td class="border border-black px-3 py-2 pr-4 text-slate-700 whitespace-nowrap">${m.created_by}</td>
                                </tr>
                            `;
                        });
                    }
                })
                .catch(err => {
                    document.getElementById('vhMovementsBody').innerHTML =
                        '<tr><td colspan="7" class="border border-black p-4 text-center text-rose-600">Failed to load movement history: ' +
                        err.message + '</td></tr>';
                });
        }

        function closeVariationHistoryModal() {
            document.getElementById('variationHistoryModal').classList.add('hidden');
        }

        // Populate and handle column select filters for Audit Movements Ledger
        function initMovementColumnFilters() {
            const rows = document.querySelectorAll('#movementsMasterTable tbody tr.movement-row');
            if (!rows.length) return;

            const dates = new Set();
            const products = new Set();
            const variations = new Set();
            const types = new Map(); // key -> display label
            const flows = new Set();
            const costs = new Set();
            const references = new Set();
            const reasons = new Set();
            const loggers = new Set();

            rows.forEach(r => {
                const date = r.getAttribute('data-date');
                if (date) dates.add(date);

                const prod = r.getAttribute('data-product');
                if (prod && prod !== '-') products.add(prod);

                const variation = r.getAttribute('data-variation');
                if (variation) variations.add(variation);

                const typeKey = r.getAttribute('data-type');
                const typeLbl = r.getAttribute('data-type-label');
                if (typeKey) types.set(typeKey, typeLbl);

                const flow = r.getAttribute('data-flow');
                if (flow) flows.add(flow);

                const cost = r.getAttribute('data-cost');
                if (cost) costs.add(cost);

                const ref = r.getAttribute('data-reference');
                if (ref && ref !== '-') references.add(ref);

                const reason = r.getAttribute('data-reason');
                if (reason) reasons.add(reason);

                const logger = r.getAttribute('data-logged-by');
                if (logger) loggers.add(logger);
            });

            const populateSelect = (selectId, setOrMap, isMap = false) => {
                const select = document.getElementById(selectId);
                if (!select) return;
                const defaultOpt = select.options[0];
                select.innerHTML = '';
                select.appendChild(defaultOpt);

                if (isMap) {
                    Array.from(setOrMap.entries()).forEach(([val, label]) => {
                        const opt = document.createElement('option');
                        opt.value = val;
                        opt.textContent = label.toUpperCase();
                        select.appendChild(opt);
                    });
                } else {
                    Array.from(setOrMap).sort().forEach(val => {
                        const opt = document.createElement('option');
                        opt.value = val;
                        opt.textContent = val;
                        select.appendChild(opt);
                    });
                }
            };

            populateSelect('colFilter_date', dates);
            populateSelect('colFilter_product', products);
            populateSelect('colFilter_variation', variations);
            populateSelect('colFilter_type', types, true);
            populateSelect('colFilter_flow', flows);
            populateSelect('colFilter_cost', costs);
            populateSelect('colFilter_reference', references);
            populateSelect('colFilter_reason', reasons);
            populateSelect('colFilter_logged_by', loggers);

            // Populate Qty Change select options
            const qtySelect = document.getElementById('colFilter_qty');
            if (qtySelect) {
                const defaultOpt = qtySelect.options[0];
                qtySelect.innerHTML = '';
                qtySelect.appendChild(defaultOpt);

                const optPos = document.createElement('option');
                optPos.value = 'positive';
                optPos.textContent = '+ Positive (Inbound)';
                qtySelect.appendChild(optPos);

                const optNeg = document.createElement('option');
                optNeg.value = 'negative';
                optNeg.textContent = '- Negative (Outbound)';
                qtySelect.appendChild(optNeg);

                // Collect distinct qty strings
                const distinctQtys = new Set();
                rows.forEach(r => {
                    const q = r.getAttribute('data-qty');
                    if (q) distinctQtys.add(q);
                });
                Array.from(distinctQtys).sort().forEach(q => {
                    const opt = document.createElement('option');
                    opt.value = q;
                    opt.textContent = q;
                    qtySelect.appendChild(opt);
                });
            }
        }

        // Multi-column filter for Movements Ledger
        function filterMovementsTable() {
            const dateVal = (document.getElementById('colFilter_date')?.value || '').toLowerCase();
            const prodVal = (document.getElementById('colFilter_product')?.value || '').toLowerCase();
            const varVal = (document.getElementById('colFilter_variation')?.value || '').toLowerCase();
            const typeVal = (document.getElementById('colFilter_type')?.value || '').toLowerCase();
            const qtyVal = (document.getElementById('colFilter_qty')?.value || '').toLowerCase();
            const flowVal = (document.getElementById('colFilter_flow')?.value || '').toLowerCase();
            const costVal = (document.getElementById('colFilter_cost')?.value || '').toLowerCase();
            const refVal = (document.getElementById('colFilter_reference')?.value || '').toLowerCase();
            const reasonVal = (document.getElementById('colFilter_reason')?.value || '').toLowerCase();
            const logVal = (document.getElementById('colFilter_logged_by')?.value || '').toLowerCase();

            const allFilters = [
                { id: 'colFilter_date', val: dateVal },
                { id: 'colFilter_product', val: prodVal },
                { id: 'colFilter_variation', val: varVal },
                { id: 'colFilter_type', val: typeVal },
                { id: 'colFilter_qty', val: qtyVal },
                { id: 'colFilter_flow', val: flowVal },
                { id: 'colFilter_cost', val: costVal },
                { id: 'colFilter_reference', val: refVal },
                { id: 'colFilter_reason', val: reasonVal },
                { id: 'colFilter_logged_by', val: logVal }
            ];

            let activeFilterCount = 0;
            allFilters.forEach(f => {
                const el = document.getElementById(f.id);
                if (f.val) {
                    activeFilterCount++;
                    if (el) {
                        el.classList.add('bg-indigo-50', 'border-indigo-400', 'font-semibold', 'text-indigo-900');
                        el.classList.remove('bg-white', 'border-slate-300', 'text-slate-700', 'font-normal');
                    }
                } else {
                    if (el) {
                        el.classList.remove('bg-indigo-50', 'border-indigo-400', 'font-semibold', 'text-indigo-900');
                        el.classList.add('bg-white', 'border-slate-300', 'text-slate-700', 'font-normal');
                    }
                }
            });

            const rows = document.querySelectorAll('#movementsMasterTable tbody tr.movement-row');
            let visibleCount = 0;

            rows.forEach(r => {
                const rDate = (r.getAttribute('data-date') || '').toLowerCase();
                const rDatetime = (r.getAttribute('data-datetime') || '').toLowerCase();
                const rProd = (r.getAttribute('data-product') || '').toLowerCase();
                const rVar = (r.getAttribute('data-variation') || '').toLowerCase();
                const rType = (r.getAttribute('data-type') || '').toLowerCase();
                const rQtySign = (r.getAttribute('data-qty-sign') || '').toLowerCase();
                const rQty = (r.getAttribute('data-qty') || '').toLowerCase();
                const rFlow = (r.getAttribute('data-flow') || '').toLowerCase();
                const rCost = (r.getAttribute('data-cost') || '').toLowerCase();
                const rRef = (r.getAttribute('data-reference') || '').toLowerCase();
                const rReason = (r.getAttribute('data-reason') || '').toLowerCase();
                const rLog = (r.getAttribute('data-logged-by') || '').toLowerCase();

                let match = true;
                if (dateVal && !rDate.includes(dateVal) && !rDatetime.includes(dateVal)) match = false;
                if (prodVal && rProd !== prodVal) match = false;
                if (varVal && rVar !== varVal) match = false;
                if (typeVal && rType !== typeVal) match = false;
                if (qtyVal) {
                    if (qtyVal === 'positive' && rQtySign !== 'positive') match = false;
                    else if (qtyVal === 'negative' && rQtySign !== 'negative') match = false;
                    else if (qtyVal !== 'positive' && qtyVal !== 'negative' && rQty !== qtyVal) match = false;
                }
                if (flowVal && rFlow !== flowVal) match = false;
                if (costVal && rCost !== costVal) match = false;
                if (refVal && rRef !== refVal) match = false;
                if (reasonVal && rReason !== reasonVal) match = false;
                if (logVal && rLog !== logVal) match = false;

                if (match) {
                    r.style.display = '';
                    visibleCount++;
                } else {
                    r.style.display = 'none';
                }
            });

            // Empty state row
            const emptyRow = document.getElementById('noMatchingMovementsRow');
            if (emptyRow) {
                emptyRow.style.display = visibleCount === 0 ? '' : 'none';
            }

            // Update badge counter
            const badge = document.getElementById('movementsCountBadge');
            if (badge) {
                badge.innerText = `Showing ${visibleCount} of ${rows.length} entries`;
            }

            // Update reset button
            const resetBtn = document.getElementById('resetMovementsFilterBtn');
            const resetCount = document.getElementById('activeFiltersCountBadge');
            if (resetBtn) {
                if (activeFilterCount > 0) {
                    resetBtn.style.display = 'inline-flex';
                    if (resetCount) resetCount.innerText = activeFilterCount;
                } else {
                    resetBtn.style.display = 'none';
                }
            }
        }

        // Reset all movement column filters
        function resetMovementFilters() {
            const filterIds = [
                'colFilter_date', 'colFilter_product', 'colFilter_variation',
                'colFilter_type', 'colFilter_qty', 'colFilter_flow',
                'colFilter_cost', 'colFilter_reference', 'colFilter_reason',
                'colFilter_logged_by'
            ];
            filterIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            filterMovementsTable();
        }

        // Auto initialize on script execution and DOM load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMovementColumnFilters);
        } else {
            initMovementColumnFilters();
        }
    </script>
@endpush
