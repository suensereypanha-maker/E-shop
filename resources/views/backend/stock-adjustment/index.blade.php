@extends('backend.layouts.app', ['title' => 'Stock Adjustments'])

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

        /* Clean Black Border Print Styling */
        @media print {
            table.dataTable {
                border-collapse: collapse !important;
                width: 100% !important;
                border: 1px solid #000000 !important;
            }
            table.dataTable th, table.dataTable td {
                border: 1px solid #000000 !important;
                padding: 6px 8px !important;
                color: #000000 !important;
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
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                <span>/</span>
                <span class="text-slate-500">Inventory</span>
                <span>/</span>
                <span class="text-slate-700 font-semibold">Stock Adjustments</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Stock Adjustments</h1>
            <p class="text-xs text-slate-500 mt-0.5">Reconcile inventory discrepancies, record damages, losses, and stock count corrections.</p>
        </div>

        <button type="button" onclick="openMultiAdjustmentModal()"
            class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm hover:shadow transition flex items-center gap-2 self-start sm:self-auto">
            <i class="bi bi-card-checklist text-base"></i>
            <span>Multi-Item Adjustment</span>
        </button>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <!-- 1. Total Adjustments -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-sliders2"></i>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500">Total Adjustments</div>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total_adjustments']) }}</div>
            </div>
        </div>

        <!-- 2. Units Added (+) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-plus-circle"></i>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500">Total Units Added (+)</div>
                <div class="text-xl font-bold text-emerald-700 mt-0.5 font-mono">+{{ number_format($stats['total_added_qty']) }} <span class="text-xs font-normal text-slate-400">pcs</span></div>
            </div>
        </div>

        <!-- 3. Units Deducted (-) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-dash-circle"></i>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500">Total Units Deducted (-)</div>
                <div class="text-xl font-bold text-rose-700 mt-0.5 font-mono">-{{ number_format($stats['total_deducted_qty']) }} <span class="text-xs font-normal text-slate-400">pcs</span></div>
            </div>
        </div>

        <!-- 4. Net Cost Impact -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500">Net Valuation Impact</div>
                <div class="text-xl font-bold font-mono mt-0.5 {{ $stats['net_cost_impact'] >= 0 ? 'text-slate-900' : 'text-rose-700' }}">
                    {{ $stats['net_cost_impact'] < 0 ? '-$' . number_format(abs($stats['net_cost_impact']), 2) : '+$' . number_format($stats['net_cost_impact'], 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Component -->
    <div class="mb-6">
        {!! $dataTable->table(['class' => 'display', 'style' => 'width:100%']) !!}
    </div>
</div>

<!-- ========================================== -->
<!-- Multi-Item Bulk Stock Adjustment Modal     -->
<!-- ========================================== -->
<div id="multiAdjustmentModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" onclick="closeMultiAdjustmentModal()"></div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <form action="{{ route('backend.stock-adjustments.store') }}" method="POST" id="multiAdjustmentForm">
                @csrf
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Multi-Item Inventory Adjustment</h3>
                        <p class="text-xs text-slate-400">Reconcile multiple items or whole shelves in a single adjustment voucher</p>
                    </div>
                    <button type="button" onclick="closeMultiAdjustmentModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Top Meta Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Adjustment Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                                class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Reason <span class="text-rose-500">*</span></label>
                            <select name="reason" required
                                class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white">
                                <option value="count_mismatch" selected>Physical Count Mismatch</option>
                                <option value="damage">Damage / Broken Goods</option>
                                <option value="lost_theft">Loss / Theft</option>
                                <option value="expired">Expired Batch</option>
                                <option value="found_stock">Found Inventory (+)</option>
                                <option value="other">Other / Reconciliation</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Reference Code</label>
                            <input type="text" name="reference_no" value="{{ $referenceNo }}" readonly
                                class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 font-mono text-slate-600">
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider text-[11px]">Adjustment Line Items</span>
                            <button type="button" onclick="addMultiItemRow()"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Add Line
                            </button>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-left text-xs text-slate-700" id="multiItemsTable">
                                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold">
                                    <tr>
                                        <th class="py-2.5 px-3 w-8 text-center">#</th>
                                        <th class="py-2.5 px-3 min-w-[200px]">Product <span class="text-rose-500">*</span></th>
                                        <th class="py-2.5 px-3 min-w-[220px]">Variation (Stock) <span class="text-rose-500">*</span></th>
                                        <th class="py-2.5 px-3 w-32">Action <span class="text-rose-500">*</span></th>
                                        <th class="py-2.5 px-3 w-24 text-center">Adjust Qty <span class="text-rose-500">*</span></th>
                                        <th class="py-2.5 px-3 w-28 text-center">New Stock</th>
                                        <th class="py-2.5 px-3 w-12 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="multiItemsTableBody" class="divide-y divide-slate-100">
                                    <!-- Injected via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Remarks / Audit Notes</label>
                        <textarea name="note" rows="2" placeholder="Optional details regarding this inventory adjustment..."
                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"></textarea>
                    </div>
                </div>

                <div class="p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="closeMultiAdjustmentModal()"
                        class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-100 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                        <i class="bi bi-check-circle"></i>
                        <span>Save & Update Inventory</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}

    <script>
        const productsData = @json($products);
        let multiRowIndex = 0;

        // Multi-Item Adjustment Modal
        function openMultiAdjustmentModal() {
            document.getElementById('multiAdjustmentModal').classList.remove('hidden');
            const tbody = document.getElementById('multiItemsTableBody');
            if (tbody.children.length === 0) {
                addMultiItemRow();
            }
        }

        function closeMultiAdjustmentModal() {
            document.getElementById('multiAdjustmentModal').classList.add('hidden');
        }

        function addMultiItemRow() {
            const tbody = document.getElementById('multiItemsTableBody');
            const idx = multiRowIndex++;

            let productOptions = '<option value="">-- Choose Product --</option>';
            productsData.forEach(p => {
                productOptions += `<option value="${p.id}">${p.name}</option>`;
            });

            const row = document.createElement('tr');
            row.id = `multiRow_${idx}`;
            row.innerHTML = `
                <td class="py-2.5 px-3 text-center text-slate-400 font-mono row-num">${tbody.children.length + 1}</td>
                <td class="py-2.5 px-3">
                    <select name="items[${idx}][product_id]" required onchange="onMultiProductChange(this, ${idx})"
                        class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none bg-white">
                        ${productOptions}
                    </select>
                </td>
                <td class="py-2.5 px-3">
                    <select name="items[${idx}][variation_id]" id="multiVar_${idx}" required disabled onchange="calculateMultiRow(${idx})"
                        class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none bg-slate-50">
                        <option value="">-- Select Product First --</option>
                    </select>
                </td>
                <td class="py-2.5 px-3">
                    <select name="items[${idx}][type]" id="multiType_${idx}" required onchange="calculateMultiRow(${idx})"
                        class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none bg-white font-semibold">
                        <option value="subtraction" selected>Deduct (-)</option>
                        <option value="addition">Add (+)</option>
                    </select>
                </td>
                <td class="py-2.5 px-3">
                    <input type="number" name="items[${idx}][quantity]" id="multiQty_${idx}" min="1" value="1" required oninput="calculateMultiRow(${idx})"
                        class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none text-center font-bold">
                </td>
                <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-700" id="multiResultStock_${idx}">
                    -
                </td>
                <td class="py-2.5 px-3 text-center">
                    <button type="button" onclick="removeMultiItemRow(${idx})" class="text-rose-500 hover:text-rose-700">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
            renumberMultiRows();
        }

        function removeMultiItemRow(idx) {
            const row = document.getElementById(`multiRow_${idx}`);
            if (row) row.remove();
            renumberMultiRows();
        }

        function renumberMultiRows() {
            const tbody = document.getElementById('multiItemsTableBody');
            Array.from(tbody.children).forEach((tr, i) => {
                const numCell = tr.querySelector('.row-num');
                if (numCell) numCell.textContent = i + 1;
            });
        }

        function onMultiProductChange(selectEl, idx) {
            const prodId = selectEl.value;
            const varSelect = document.getElementById(`multiVar_${idx}`);
            varSelect.innerHTML = '<option value="">-- Choose Variation --</option>';

            if (!prodId) {
                varSelect.disabled = true;
                varSelect.classList.add('bg-slate-50');
                document.getElementById(`multiResultStock_${idx}`).textContent = '-';
                return;
            }

            const product = productsData.find(p => p.id == prodId);
            if (!product || !product.variations || product.variations.length === 0) {
                varSelect.innerHTML = '<option value="">No variations</option>';
                varSelect.disabled = true;
                return;
            }

            product.variations.forEach(v => {
                const color = v.color ? v.color.name : 'Standard';
                const size = v.size ? v.size.name : 'Standard';
                const stock = v.stock ?? 0;
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.dataset.stock = stock;
                opt.textContent = `${color} / ${size} [Stock: ${stock}]`;
                varSelect.appendChild(opt);
            });

            varSelect.disabled = false;
            varSelect.classList.remove('bg-slate-50');
            calculateMultiRow(idx);
        }

        function calculateMultiRow(idx) {
            const varSelect = document.getElementById(`multiVar_${idx}`);
            const resultCell = document.getElementById(`multiResultStock_${idx}`);
            const selectedOpt = varSelect.options[varSelect.selectedIndex];

            if (!selectedOpt || !selectedOpt.value) {
                resultCell.textContent = '-';
                return;
            }

            const currentStock = parseInt(selectedOpt.dataset.stock || 0);
            const type = document.getElementById(`multiType_${idx}`).value;
            const qty = parseInt(document.getElementById(`multiQty_${idx}`).value || 1);

            const finalStock = type === 'addition' ? (currentStock + qty) : Math.max(0, currentStock - qty);
            resultCell.textContent = `${finalStock} pcs`;
        }
    </script>
@endpush
