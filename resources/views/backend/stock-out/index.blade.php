@extends('backend.layouts.app', ['title' => 'Stock Out Management'])

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

        /* Pagination: No background, no border, show only numbers */
        div.dataTables_wrapper div.dataTables_paginate .paginate_button,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:active {
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

            table.dataTable th,
            table.dataTable td {
                border: 1px solid #000000 !important;
                padding: 8px 12px !important;
                text-align: left !important;
                color: #000000 !important;
            }

            table.dataTable th {
                background-color: #f1f5f9 !important;
                font-weight: 700 !important;
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
    <div class="space-y-6">
        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-500">Inventory</span>
                    <span>/</span>
                    <span class="text-slate-700 font-semibold">Stock Out</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Stock Out Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">Track and record outbound stock dispatches, store usage, damages, samples, and vendor returns.</p>
            </div>
            <button type="button" onclick="openMultiStockOutModal()"
                class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm hover:shadow transition flex items-center gap-2 self-start sm:self-auto">
                <i class="bi bi-card-checklist text-base"></i>
                <span>Multi-Item Dispatch</span>
            </button>
        </div>

        <!-- Summary Metric Cards (3 Cards matching Stock In) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="text-xs font-medium text-slate-500">Total Stock-Out Records</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total_dispatches']) }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-boxes"></i>
                </div>
                <div>
                    <div class="text-xs font-medium text-slate-500">Total Quantity Dispatched</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">-{{ number_format($stats['total_dispatched_qty']) }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="text-xs font-medium text-slate-500">Total Dispatched Valuation</div>
                    <div class="text-xl font-bold text-emerald-700 mt-0.5 font-mono">${{ number_format($stats['total_dispatched_cost'], 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div id="ajaxAlertContainer"></div>

        @if (session('success'))
            <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- DataTable Container -->
        <div class="mb-6">
            {!! $dataTable->table(['class' => 'display', 'style' => 'width:100%']) !!}
        </div>
    </div>

    <!-- ========================================== -->
    <!-- Multi-Item Bulk Stock Out Dispatch Modal   -->
    <!-- ========================================== -->
    <div id="multiStockOutModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeMultiStockOutModal()"></div>

            <!-- Modal Panel -->
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-200">
                <form action="{{ route('backend.stock-outs.store') }}" method="POST" id="multiStockOutForm">
                    @csrf
                    <div class="p-6">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 leading-6">Multi-Item Stock Out Dispatch</h3>
                                    <p class="text-xs text-slate-500">Deduct multiple items or entire material batches in a single dispatch slip.</p>
                                </div>
                            </div>
                            <button type="button" onclick="closeMultiStockOutModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                                <i class="bi bi-x-lg text-lg"></i>
                            </button>
                        </div>

                        <!-- Top Meta Fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Reference No</label>
                                <input type="text" name="reference_no" value="{{ $referenceNo }}" readonly
                                    class="w-full bg-slate-50 text-slate-700 font-mono text-xs px-3 py-2 rounded-lg border border-slate-200 cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Dispatch Date <span class="text-rose-500">*</span></label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                                    class="w-full bg-white text-slate-800 text-xs px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Reason <span class="text-rose-500">*</span></label>
                                <select name="reason" required
                                    class="w-full bg-white text-slate-800 text-xs px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                                    <option value="sale_dispatch">Sale / Direct Dispatch</option>
                                    <option value="damage_scrap">Damaged / Scrap</option>
                                    <option value="internal_use">Internal / Store Use</option>
                                    <option value="sample">Sample / Promotion</option>
                                    <option value="return_supplier">Return to Supplier</option>
                                    <option value="expired">Expired Items</option>
                                    <option value="loss_theft">Lost / Theft</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Recipient / Department</label>
                                <input type="text" name="recipient_name" placeholder="e.g. Retail Floor, QA"
                                    class="w-full bg-white text-slate-800 text-xs px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                            </div>
                        </div>

                        <!-- Line Items Table Section -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Line Items To Deduct</h4>
                                <button type="button" onclick="addModalRow()"
                                    class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition inline-flex items-center gap-1.5">
                                    <i class="bi bi-plus-circle text-xs"></i> Add Item
                                </button>
                            </div>

                            <div class="border border-slate-200 rounded-xl overflow-x-auto bg-slate-50/50">
                                <table class="w-full text-left text-xs" id="modalItemsTable">
                                    <thead class="bg-slate-100 text-slate-600 border-b border-slate-200 uppercase font-semibold text-[11px]">
                                        <tr>
                                            <th class="p-2.5 pl-3">Product</th>
                                            <th class="p-2.5">Variation</th>
                                            <th class="p-2.5 text-center w-24">On Hand</th>
                                            <th class="p-2.5 text-center w-28">Deduct Qty</th>
                                            <th class="p-2.5 text-right w-28">Unit Cost ($)</th>
                                            <th class="p-2.5 text-right w-28">Subtotal ($)</th>
                                            <th class="p-2.5 w-12 text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalItemsBody" class="divide-y divide-slate-200 bg-white">
                                        <!-- Dynamic Rows -->
                                    </tbody>
                                    <tfoot class="bg-slate-50 border-t border-slate-200 font-semibold text-slate-800 text-xs">
                                        <tr>
                                            <td colspan="3" class="p-2.5 pl-3 text-right">Total:</td>
                                            <td class="p-2.5 text-center text-indigo-700 font-bold" id="modalTotalQtyDisplay">0 pcs</td>
                                            <td></td>
                                            <td class="p-2.5 text-right font-mono font-bold text-slate-900" id="modalTotalCostDisplay">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Remarks / Note -->
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Dispatch Note / Remarks</label>
                            <textarea name="note" rows="2" placeholder="Optional dispatch details, tracking number, or department note..."
                                class="w-full bg-white text-slate-800 text-xs p-3 rounded-lg border border-slate-300 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" onclick="closeMultiStockOutModal()"
                            class="px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" id="submitMultiModalBtn"
                            class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm hover:shadow transition flex items-center gap-1.5">
                            <i class="bi bi-check2-circle text-sm"></i>
                            <span>Confirm & Dispatch</span>
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
        const quickProductsData = @json($products);
        let modalRowIndex = 0;

        // ==========================================
        // Multi-Item Modal Functions
        // ==========================================
        function openMultiStockOutModal() {
            document.getElementById('multiStockOutModal').classList.remove('hidden');
            const tbody = document.getElementById('modalItemsBody');
            if (tbody.children.length === 0) {
                addModalRow();
            }
        }

        function closeMultiStockOutModal() {
            document.getElementById('multiStockOutModal').classList.add('hidden');
        }

        function addModalRow() {
            modalRowIndex++;
            const idx = modalRowIndex;
            const tbody = document.getElementById('modalItemsBody');

            let productOptions = '<option value="">-- Choose Product --</option>';
            quickProductsData.forEach(p => {
                productOptions += `<option value="${p.id}">${escapeHtml(p.name)}</option>`;
            });

            const tr = document.createElement('tr');
            tr.id = `modalRow_${idx}`;
            tr.className = 'hover:bg-slate-50 transition-colors';
            tr.innerHTML = `
                <td class="p-2.5 pl-3">
                    <select name="items[${idx}][product_id]" required
                        onchange="onModalProductChange(${idx}, this.value)"
                        class="w-full text-xs p-2 rounded-lg border border-slate-300 focus:outline-none focus:border-indigo-500 font-medium bg-white">
                        ${productOptions}
                    </select>
                </td>
                <td class="p-2.5">
                    <select name="items[${idx}][variation_id]" id="modalVarSelect_${idx}" required disabled
                        onchange="onModalVariationChange(${idx}, this)"
                        class="w-full text-xs p-2 rounded-lg border border-slate-300 focus:outline-none focus:border-indigo-500 disabled:bg-slate-100 disabled:text-slate-400 bg-white">
                        <option value="">-- Choose Product First --</option>
                    </select>
                </td>
                <td class="p-2.5 text-center font-mono font-semibold text-slate-600" id="modalOnHand_${idx}">-</td>
                <td class="p-2.5">
                    <input type="number" name="items[${idx}][quantity]" id="modalQty_${idx}" value="1" min="1" step="1" required
                        oninput="calculateModalRow(${idx})"
                        class="w-full text-xs p-2 text-center rounded-lg border border-slate-300 focus:outline-none focus:border-indigo-500 font-bold text-slate-900 bg-white">
                </td>
                <td class="p-2.5 text-right font-mono text-slate-700">
                    <input type="number" name="items[${idx}][unit_cost]" id="modalUnitCost_${idx}" value="0.00" min="0" step="0.01" required
                        oninput="calculateModalRow(${idx})"
                        class="w-24 text-xs p-1.5 text-right rounded-lg border border-slate-300 focus:outline-none focus:border-indigo-500 font-mono bg-white">
                </td>
                <td class="p-2.5 text-right font-mono font-bold text-slate-900" id="modalSubtotalDisplay_${idx}">$0.00</td>
                <td class="p-2.5 text-center">
                    <button type="button" onclick="removeModalRow(${idx})" class="text-slate-400 hover:text-rose-600 p-1 rounded transition" title="Remove Row">
                        <i class="bi bi-trash3 text-sm"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            recalculateModalTotals();
        }

        function removeModalRow(idx) {
            const tr = document.getElementById(`modalRow_${idx}`);
            if (tr) {
                tr.remove();
                recalculateModalTotals();
            }
        }

        function onModalProductChange(idx, productId) {
            const varSelect = document.getElementById(`modalVarSelect_${idx}`);
            const onHandSpan = document.getElementById(`modalOnHand_${idx}`);
            const costInput = document.getElementById(`modalUnitCost_${idx}`);

            varSelect.innerHTML = '<option value="">-- Select Variation --</option>';
            onHandSpan.innerText = '-';
            if (costInput) costInput.value = '0.00';

            if (!productId) {
                varSelect.disabled = true;
                calculateModalRow(idx);
                return;
            }

            const product = quickProductsData.find(p => p.id == productId);
            if (!product) return;

            const cost = parseFloat(product.cost_price || 0);
            if (costInput) costInput.value = cost.toFixed(2);

            if (product.variations && product.variations.length > 0) {
                varSelect.disabled = false;
                product.variations.forEach(v => {
                    const colorName = v.color ? (v.color.name || v.color) : 'Standard';
                    const sizeName = v.size ? (v.size.name || v.size) : 'Standard';
                    const stock = v.stock || 0;
                    varSelect.innerHTML += `<option value="${v.id}" data-stock="${stock}">${colorName} / ${sizeName}</option>`;
                });

                if (product.variations.length === 1) {
                    varSelect.value = product.variations[0].id;
                    onModalVariationChange(idx, varSelect);
                }
            } else {
                varSelect.innerHTML = '<option value="">(No variations found)</option>';
                varSelect.disabled = true;
            }

            calculateModalRow(idx);
        }

        function onModalVariationChange(idx, selectEl) {
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const onHandSpan = document.getElementById(`modalOnHand_${idx}`);

            if (selectedOpt && selectedOpt.dataset.stock !== undefined) {
                const stock = parseInt(selectedOpt.dataset.stock) || 0;
                onHandSpan.innerText = `${stock} pcs`;
                onHandSpan.className = stock > 0 
                    ? 'p-2.5 text-center font-mono font-bold text-emerald-700' 
                    : 'p-2.5 text-center font-mono font-bold text-rose-600';
            } else {
                onHandSpan.innerText = '-';
                onHandSpan.className = 'p-2.5 text-center font-mono font-semibold text-slate-600';
            }

            calculateModalRow(idx);
        }

        function calculateModalRow(idx) {
            const qty = parseFloat(document.getElementById(`modalQty_${idx}`)?.value) || 0;
            const cost = parseFloat(document.getElementById(`modalUnitCost_${idx}`)?.value) || 0;
            const subtotal = Math.max(0, qty) * cost;

            const subDisplay = document.getElementById(`modalSubtotalDisplay_${idx}`);
            if (subDisplay) subDisplay.innerText = `$${subtotal.toFixed(2)}`;

            recalculateModalTotals();
        }

        function recalculateModalTotals() {
            let totalQty = 0;
            let totalCost = 0;

            const rows = document.querySelectorAll('#modalItemsBody tr');
            rows.forEach(row => {
                const qtyInput = row.querySelector('[id^="modalQty_"]');
                const costInput = row.querySelector('[id^="modalUnitCost_"]');
                if (qtyInput && costInput) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const cost = parseFloat(costInput.value) || 0;
                    totalQty += qty;
                    totalCost += (qty * cost);
                }
            });

            const totalQtyEl = document.getElementById('modalTotalQtyDisplay');
            const totalCostEl = document.getElementById('modalTotalCostDisplay');
            if (totalQtyEl) totalQtyEl.innerText = `${totalQty} pcs`;
            if (totalCostEl) totalCostEl.innerText = `$${totalCost.toFixed(2)}`;
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text || '').replace(/[&<>"']/g, m => map[m]);
        }

    </script>
@endpush
