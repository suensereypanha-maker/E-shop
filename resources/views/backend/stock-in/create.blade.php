@extends('backend.layouts.app', ['title' => 'New Stock In'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                <span>/</span>
                <a href="{{ route('backend.stock-ins.index') }}" class="hover:text-indigo-600">Stock In</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">New</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Create Stock In</h1>
            <p class="text-xs text-slate-500 mt-0.5">Receive incoming inventory shipments and adjust product variation stock balances.</p>
        </div>
        <a href="{{ route('backend.stock-ins.index') }}"
            class="px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Stock-In List</span>
        </a>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i class="bi bi-exclamation-octagon-fill text-rose-600"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('backend.stock-ins.store') }}" method="POST" id="stockInForm">
        @csrf

        <div class="space-y-6">
            <!-- 1. Header Information Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2 pb-2 border-b border-slate-100">
                    <i class="bi bi-file-earmark-text text-indigo-600"></i>
                    <span>Shipment & Supplier Details</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Reference No <span class="text-slate-400 font-normal">(Auto)</span>
                        </label>
                        <input type="text" name="reference_no" value="{{ old('reference_no', $referenceNo) }}" readonly
                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-600 font-mono font-bold outline-none cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Supplier <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <select name="supplier_id" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white">
                            <option value="">-- Select Supplier --</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} {{ $supplier->company_name ? "({$supplier->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Supplier Invoice / Ref No <span class="text-slate-400 font-normal">(Auto if empty)</span>
                        </label>
                        <input type="text" name="supplier_invoice_no" value="{{ old('supplier_invoice_no', $supplierInvoiceNo ?? '') }}" placeholder="e.g. {{ $supplierInvoiceNo ?? 'INV-' . date('Ymd') . '-0001' }}"
                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Received Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="received_date" value="{{ old('received_date', date('Y-m-d')) }}" required
                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes / Remarks</label>
                    <textarea name="note" rows="2" placeholder="Add any details about this delivery, container number, condition..."
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">{{ old('note') }}</textarea>
                </div>
            </div>

            <!-- 2. Items Table Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 mb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="bi bi-box-seam text-indigo-600"></i>
                            <span>Inward Stock Items</span>
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Select the product and specific color/size variation to receive.</p>
                    </div>

                    <!-- Quick SKU Search Input -->
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <i class="bi bi-upc-scan absolute left-3 top-2 text-xs text-slate-400"></i>
                            <input type="text" id="quickSkuInput" placeholder="Scan SKU / Barcode..."
                                class="text-xs pl-8 pr-3 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none w-48 sm:w-60">
                        </div>
                        <button type="button" onclick="addRow()"
                            class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1">
                            <i class="bi bi-plus-lg"></i>
                            <span>Add Row</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700" id="itemsTable">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 font-semibold bg-slate-50/50">
                                <th class="py-2.5 px-3 w-8">#</th>
                                <th class="py-2.5 px-3 min-w-[200px]">Product <span class="text-rose-500">*</span></th>
                                <th class="py-2.5 px-3 min-w-[240px]">Variation (Color / Size) <span class="text-rose-500">*</span></th>
                                <th class="py-2.5 px-3 w-28 text-center">Current Stock</th>
                                <th class="py-2.5 px-3 w-28 text-center">Inward Qty <span class="text-rose-500">*</span></th>
                                <th class="py-2.5 px-3 w-32 text-right">Unit Cost ($) <span class="text-rose-500">*</span></th>
                                <th class="py-2.5 px-3 w-32 text-right">Subtotal ($)</th>
                                <th class="py-2.5 px-3 w-16 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody" class="divide-y divide-slate-100">
                            <!-- Rows injected by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty state if no rows -->
                <div id="noRowsState" class="text-center py-10 text-slate-400">
                    <i class="bi bi-inbox text-3xl mb-1 block"></i>
                    <p class="text-xs">No items added yet. Click <strong>"Add Row"</strong> or scan an SKU above.</p>
                </div>

                <!-- Table Summary Footer -->
                <div class="mt-4 pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 p-4 rounded-xl">
                    <div class="text-xs text-slate-500">
                        Total Line Items: <span id="summaryRowCount" class="font-bold text-slate-800">0</span>
                    </div>

                    <div class="flex items-center gap-6 justify-end">
                        <div>
                            <span class="text-xs text-slate-500 block">Total Quantity:</span>
                            <span id="summaryTotalQty" class="text-base font-bold text-slate-900 font-mono">0 pcs</span>
                        </div>
                        <div class="pl-6 border-l border-slate-300">
                            <span class="text-xs text-slate-500 block">Grand Total Valuation:</span>
                            <span id="summaryGrandTotal" class="text-lg font-extrabold text-emerald-700 font-mono">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('backend.stock-ins.index') }}"
                    class="px-4 py-2.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition">
                    Cancel
                </a>
                <button type="submit" id="submitBtn"
                    class="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm hover:shadow transition flex items-center gap-2">
                    <i class="bi bi-check2-circle text-base"></i>
                    <span>Save & Update Inventory</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Embedded Products JSON Cache for Instant Client-Side Row Populating -->
<script>
    const productsData = @json($products);
    let rowCounter = 0;

    function renderEmptyState() {
        const tbody = document.getElementById('itemsTableBody');
        const emptyState = document.getElementById('noRowsState');
        if (tbody.children.length === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }
    }

    function addRow(preselectedProductId = null, preselectedVariationId = null, prefilledQty = 1) {
        rowCounter++;
        const rowId = `item-row-${rowCounter}`;
        const tbody = document.getElementById('itemsTableBody');

        let productOptions = '<option value="">-- Choose Product --</option>';
        productsData.forEach(p => {
            const isSelected = preselectedProductId && preselectedProductId == p.id ? 'selected' : '';
            productOptions += `<option value="${p.id}" ${isSelected}>${p.name}</option>`;
        });

        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.className = 'hover:bg-slate-50/70 transition-colors';
        tr.innerHTML = `
            <td class="py-3 px-3 text-slate-400 font-mono text-center row-index"></td>
            <td class="py-3 px-3">
                <select name="items[${rowCounter}][product_id]" required
                    onchange="onProductChange(${rowCounter}, this.value)"
                    class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white product-select">
                    ${productOptions}
                </select>
            </td>
            <td class="py-3 px-3">
                <select name="items[${rowCounter}][variation_id]" required
                    onchange="onVariationChange(${rowCounter}, this.value)"
                    class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white variation-select" disabled>
                    <option value="">-- First Select Product --</option>
                </select>
            </td>
            <td class="py-3 px-3 text-center">
                <span class="current-stock-badge inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    -
                </span>
            </td>
            <td class="py-3 px-3">
                <input type="number" name="items[${rowCounter}][quantity]" value="${prefilledQty}" min="1" step="1" required
                    oninput="calculateRow(${rowCounter})"
                    class="w-full text-xs text-center px-2 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none qty-input font-bold text-slate-900">
            </td>
            <td class="py-3 px-3">
                <input type="number" name="items[${rowCounter}][unit_cost]" value="0.00" min="0" step="0.01" required
                    oninput="calculateRow(${rowCounter})"
                    class="w-full text-xs text-right px-2 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none cost-input font-mono">
            </td>
            <td class="py-3 px-3 text-right">
                <span class="subtotal-display font-mono font-bold text-slate-900 text-xs">$0.00</span>
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removeRow('${rowId}')"
                    class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition" title="Remove Item">
                    <i class="bi bi-trash3 text-sm"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        reindexRows();
        renderEmptyState();

        if (preselectedProductId) {
            onProductChange(rowCounter, preselectedProductId, preselectedVariationId);
        }

        return rowCounter;
    }

    function removeRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            reindexRows();
            recalculateGrandTotals();
            renderEmptyState();
        }
    }

    function reindexRows() {
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach((r, idx) => {
            const indexCell = r.querySelector('.row-index');
            if (indexCell) indexCell.textContent = idx + 1;
        });
        document.getElementById('summaryRowCount').textContent = rows.length;
    }

    function onProductChange(rowIndex, productId, preselectVariationId = null) {
        const row = document.getElementById(`item-row-${rowIndex}`);
        if (!row) return;

        const variationSelect = row.querySelector('.variation-select');
        const stockBadge = row.querySelector('.current-stock-badge');
        const costInput = row.querySelector('.cost-input');

        variationSelect.innerHTML = '<option value="">-- Choose Variation --</option>';
        stockBadge.textContent = '-';

        if (!productId) {
            variationSelect.disabled = true;
            costInput.value = '0.00';
            calculateRow(rowIndex);
            return;
        }

        const product = productsData.find(p => p.id == productId);
        if (!product) return;

        // Auto update default unit cost from product cost_price
        if (product.cost_price !== undefined && product.cost_price !== null && product.cost_price !== '') {
            costInput.value = parseFloat(product.cost_price).toFixed(2);
        } else {
            costInput.value = '0.00';
        }

        if (!product.variations || product.variations.length === 0) {
            variationSelect.innerHTML = '<option value="">(No variations found for this product)</option>';
            variationSelect.disabled = true;
            return;
        }

        variationSelect.disabled = false;
        product.variations.forEach(v => {
            const colorName = v.color ? v.color.name : 'N/A';
            const sizeName = v.size ? v.size.name : 'N/A';
            const sku = v.sku || 'No SKU';
            const isSelected = preselectVariationId && preselectVariationId == v.id ? 'selected' : '';

            variationSelect.innerHTML += `
                <option value="${v.id}" data-stock="${v.stock}" ${isSelected}>
                    ${colorName} / ${sizeName}
                </option>
            `;
        });

        // If only 1 variation exists or preselected, auto-select it
        if (preselectVariationId) {
            variationSelect.value = preselectVariationId;
            onVariationChange(rowIndex, preselectVariationId);
        } else if (product.variations.length === 1) {
            variationSelect.value = product.variations[0].id;
            onVariationChange(rowIndex, product.variations[0].id);
        }

        calculateRow(rowIndex);
    }

    function onVariationChange(rowIndex, variationId) {
        const row = document.getElementById(`item-row-${rowIndex}`);
        if (!row) return;

        const variationSelect = row.querySelector('.variation-select');
        const stockBadge = row.querySelector('.current-stock-badge');
        const selectedOption = variationSelect.options[variationSelect.selectedIndex];

        if (selectedOption && selectedOption.dataset.stock !== undefined) {
            const currentStock = parseInt(selectedOption.dataset.stock);
            stockBadge.textContent = `${currentStock} pcs`;
            if (currentStock <= 0) {
                stockBadge.className = 'current-stock-badge inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-rose-50 text-rose-700 border border-rose-200';
            } else {
                stockBadge.className = 'current-stock-badge inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
            }
        } else {
            stockBadge.textContent = '-';
            stockBadge.className = 'current-stock-badge inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-600 border border-slate-200';
        }
    }

    function calculateRow(rowIndex) {
        const row = document.getElementById(`item-row-${rowIndex}`);
        if (!row) return;

        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        const subtotal = qty * cost;

        row.querySelector('.subtotal-display').textContent = `$${subtotal.toFixed(2)}`;
        recalculateGrandTotals();
    }

    function recalculateGrandTotals() {
        const rows = document.querySelectorAll('#itemsTableBody tr');
        let totalQty = 0;
        let grandTotal = 0;

        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.qty-input')?.value) || 0;
            const cost = parseFloat(r.querySelector('.cost-input')?.value) || 0;
            totalQty += qty;
            grandTotal += (qty * cost);
        });

        document.getElementById('summaryTotalQty').textContent = `${totalQty} pcs`;
        document.getElementById('summaryGrandTotal').textContent = `$${grandTotal.toFixed(2)}`;
    }

    // SKU / Barcode Quick Scanner Handler
    document.getElementById('quickSkuInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const skuVal = this.value.trim().toLowerCase();
            if (!skuVal) return;

            let foundProduct = null;
            let foundVariation = null;

            for (const prod of productsData) {
                if (prod.variations) {
                    const matchVar = prod.variations.find(v => v.sku && v.sku.toLowerCase() === skuVal);
                    if (matchVar) {
                        foundProduct = prod;
                        foundVariation = matchVar;
                        break;
                    }
                }
            }

            if (foundProduct && foundVariation) {
                // Check if already in rows -> increment qty
                let existingRow = null;
                document.querySelectorAll('#itemsTableBody tr').forEach(r => {
                    const varSelect = r.querySelector('.variation-select');
                    if (varSelect && varSelect.value == foundVariation.id) {
                        existingRow = r;
                    }
                });

                if (existingRow) {
                    const qtyInput = existingRow.querySelector('.qty-input');
                    qtyInput.value = parseInt(qtyInput.value || 0) + 1;
                    const rIdMatch = existingRow.id.match(/\d+$/);
                    if (rIdMatch) calculateRow(rIdMatch[0]);
                } else {
                    addRow(foundProduct.id, foundVariation.id, 1);
                }

                this.value = '';
            } else {
                alert(`No product variation found with SKU: ${skuVal}`);
            }
        }
    });

    // Form submission validation
    document.getElementById('stockInForm').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#itemsTableBody tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to stock in.');
            return false;
        }

        let hasError = false;
        rows.forEach(r => {
            const varSelect = r.querySelector('.variation-select');
            if (!varSelect || !varSelect.value) {
                hasError = true;
                varSelect.classList.add('border-rose-500');
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('Please select a specific variation for all rows.');
            return false;
        }
    });

    // Initialize with 1 empty row on load
    document.addEventListener('DOMContentLoaded', () => {
        addRow();
    });
</script>
@endsection
