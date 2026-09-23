@extends('backend.layouts.app', ['title' => 'Stock Out ' . $stockOut->reference_no])

@push('styles')
    <style>
        /* Solid black border table styling */
        .table-black,
        .table-black th,
        .table-black td {
            border: 1px solid #000000 !important;
            border-collapse: collapse !important;
        }

        @media print {
            @page {
                size: auto;
                margin: 0mm;
            }

            header,
            aside,
            nav,
            footer,
            .no-print {
                display: none !important;
            }

            /* Ensure no backgrounds are printed */
            *,
            *::before,
            *::after {
                background: transparent !important;
                background-color: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            html,
            body {
                height: auto !important;
                min-height: 100% !important;
                overflow: visible !important;
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Unset fixed height and overflow traps so all pages print without cutoff */
            .h-screen,
            .min-h-screen {
                height: auto !important;
                min-height: auto !important;
            }

            .overflow-hidden,
            .overflow-y-auto,
            .overflow-x-auto,
            .overflow-x-hidden {
                overflow: visible !important;
                height: auto !important;
                max-height: none !important;
            }

            .w-0 {
                width: 100% !important;
            }

            .flex-1 {
                flex: none !important;
                width: 100% !important;
            }

            main {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                overflow: visible !important;
                height: auto !important;
                background: transparent !important;
            }

            .print-full-width {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .print-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                background: #ffffff !important;
                background-color: #ffffff !important;
            }

            .table-black,
            .table-black thead,
            .table-black tbody,
            .table-black tr,
            .table-black th,
            .table-black td,
            table,
            thead,
            tbody,
            tr,
            th,
            td {
                background: transparent !important;
                background-color: transparent !important;
                border: 1px solid #000000 !important;
                border-collapse: collapse !important;
                color: #000000 !important;
            }

            .text-white {
                color: #000000 !important;
            }

            .print-signatures {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                page-break-inside: avoid;
            }

            /* Flow tables and multi-page documents smoothly */
            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }
    </style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12 print-full-width print:max-w-none print:w-full print:p-0 print:m-0">
    <!-- Breadcrumb & Actions Bar (Hidden on Print) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 no-print">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                <span>/</span>
                <a href="{{ route('backend.stock-outs.index') }}" class="hover:text-indigo-600">Stock Out</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ $stockOut->reference_no }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Stock Out Dispatch Slip</h1>
                @if ($stockOut->status === 'dispatched')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Dispatched
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Cancelled
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('backend.stock-outs.index') }}"
                class="px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <i class="bi bi-arrow-left"></i>
                <span>Back to List</span>
            </a>

            <button type="button" onclick="window.print()"
                class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <i class="bi bi-printer"></i>
                <span>Print Dispatch Slip</span>
            </button>

            @if ($stockOut->status !== 'cancelled')
                <form method="POST" action="{{ route('backend.stock-outs.cancel', $stockOut->id) }}"
                    onsubmit="return confirm('WARNING: Are you sure you want to cancel this Stock Out? The deducted quantities will be restored back to product inventory!')">
                    @csrf
                    <button type="submit"
                        class="px-3.5 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel & Restore Stock</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between no-print">
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
        <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between no-print">
            <div class="flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>
    @endif

    <!-- Dispatch Slip Card (Exact same as Stock In) -->
    <div class="rounded-2xl p-8 print-container space-y-8">
        <!-- Slip Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 print:bg-transparent print:border print:border-black print:text-black flex items-center justify-center text-white text-base">
                        <i class="bi bi-shop"></i>
                    </div>
                    <span class="text-xl font-black text-slate-900 tracking-tight">E-SHOP INVENTORY</span>
                </div>
                <p class="text-xs text-slate-500">Warehouse & Stock Outward Slip</p>
                <div class="mt-3 text-xs text-slate-600 space-y-0.5 font-mono">
                    <div>Ref No: <strong class="text-indigo-600 print:text-black">{{ $stockOut->reference_no }}</strong></div>
                    <div>Primary Reason: <strong>{{ $stockOut->reason_label }}</strong></div>
                    <div>Date:
                        <strong>{{ $stockOut->date ? $stockOut->date->format('F d, Y') : '-' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Recipient / Dispatch Info Box (Right Aligned, exactly like Stock In Supplier Details) -->
            <div class="p-2 sm:p-0 text-right text-xs text-slate-700 sm:w-80 ml-auto flex flex-col items-end print:text-right print:items-end">
                <div class="font-bold text-slate-900 uppercase tracking-wider text-[10px] text-indigo-600 print:text-black mb-1.5">
                    Recipient Details
                </div>
                @if ($stockOut->recipient_name)
                    <div class="font-bold text-sm text-slate-900">{{ $stockOut->recipient_name }}</div>
                    <div class="text-slate-600">Dispatched for: {{ $stockOut->reason_label }}</div>
                    @if ($stockOut->note)
                        <div class="text-slate-500 mt-1 text-[11px]">{{ Str::limit($stockOut->note, 80) }}</div>
                    @endif
                @else
                    <div class="font-bold text-sm text-slate-900">Internal / Store Use</div>
                    <div class="text-slate-600">{{ $stockOut->reason_label }}</div>
                    <div class="italic text-slate-400 mt-1">Direct Outward / Internal Department</div>
                @endif
            </div>
        </div>

        <!-- Line Items Table (Solid Black Border, exact same as Stock In) -->
        <div class="overflow-x-auto my-2">
            <table class="w-full text-left text-[11px] border-collapse border border-black table-black bg-white print:bg-transparent">
                <thead class="bg-slate-100 print:bg-transparent text-slate-900 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="border border-black py-1.5 px-2 w-10 text-center">NO</th>
                        <th class="border border-black py-1.5 px-2">PRODUCT</th>
                        <th class="border border-black py-1.5 px-2">BRAND</th>
                        <th class="border border-black py-1.5 px-2">VARIATION</th>
                        <th class="border border-black py-1.5 px-2 text-center w-24">STOCK BEFORE</th>
                        <th class="border border-black py-1.5 px-2 text-center w-24">STOCK AFTER</th>
                        <th class="border border-black py-1.5 px-2 text-center w-24">OUTWARD QTY</th>
                        <th class="border border-black py-1.5 px-2 text-right w-24">UNIT COST</th>
                        <th class="border border-black py-1.5 px-2 text-right w-24">SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody class="bg-white print:bg-transparent">
                    @foreach ($stockOut->items as $index => $item)
                        @php
                            $variation = $item->variation;
                            $colorName = optional(optional($variation)->color)->name ?? 'Standard';
                            $sizeName = optional(optional($variation)->size)->name ?? 'Standard';
                            $brandName = optional(optional($item->product)->brand)->name ?: '-';
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="border border-black py-1.5 px-2 text-center text-slate-600 font-mono text-[11px]">{{ $index + 1 }}</td>
                            <td class="border border-black py-1.5 px-2 text-[11px]">
                                <div class="font-bold text-slate-900 leading-tight">
                                    {{ optional($item->product)->name ?? 'Product Deleted' }}
                                </div>
                                @if (optional(optional($item->product)->category)->name)
                                    <div class="text-[10px] text-slate-500 leading-tight">
                                        {{ optional($item->product->category)->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="border border-black py-1.5 px-2 text-slate-800 font-medium text-[11px] whitespace-nowrap">
                                {{ $brandName }}
                            </td>
                            <td class="border border-black py-1.5 px-2 text-slate-700 text-[11px] whitespace-nowrap">
                                {{ $colorName }} / {{ $sizeName }}
                            </td>
                            <td class="border border-black py-1.5 px-2 text-center font-mono text-slate-700 text-[11px]">
                                {{ number_format($item->stock_before) }}
                            </td>
                            <td class="border border-black py-1.5 px-2 text-center font-mono font-bold text-emerald-700 text-[11px]">
                                {{ number_format($item->stock_after) }}
                            </td>
                            <td class="border border-black py-1.5 px-2 text-center font-mono font-bold text-rose-700 text-[11px]">
                                -{{ number_format($item->quantity) }}
                            </td>
                            <td class="border border-black py-1.5 px-2 text-right font-mono text-slate-800 text-[11px]">
                                ${{ number_format($item->unit_cost, 2) }}
                            </td>
                            <td class="border border-black py-1.5 px-2 text-right font-mono font-bold text-slate-900 text-[11px]">
                                ${{ number_format($item->total_cost, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary & Totals (Exact same style as Stock In) -->
        <div class="pt-3 flex flex-col sm:flex-row justify-between gap-6">
            <div class="text-xs text-slate-500 sm:w-1/2">
                @if ($stockOut->note)
                    <div class="font-semibold text-slate-700 mb-1">Remarks / Notes:</div>
                    <p class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-slate-600 leading-relaxed">
                        {{ $stockOut->note }}</p>
                @endif
                <div class="mt-4 text-[11px] text-slate-400">
                    Recorded by: <strong
                        class="text-slate-700 font-semibold">{{ optional($stockOut->creator)->name ?? (auth()->user()?->name ?? 'Administrator') }}</strong>
                    on {{ $stockOut->created_at ? $stockOut->created_at->format('Y-m-d H:i') : '' }}
                </div>
            </div>

            <div class="sm:w-80 space-y-2 p-4 rounded-xl text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Total Units Dispatched:</span>
                    <strong class="font-mono text-slate-900">-{{ number_format($stockOut->total_quantity) }}</strong>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Items Count:</span>
                    <strong class="font-mono text-slate-900">{{ $stockOut->items->count() }} items</strong>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                    <span class="font-bold text-slate-800 text-sm">Grand Total Cost:</span>
                    <strong class="font-mono text-xl font-extrabold text-emerald-700">
                        ${{ number_format($stockOut->total_cost, 2) }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- Signatures (For Physical Paper Audit, exactly like Stock In) -->
        <div class="hidden print-signatures grid-cols-2 gap-12 pt-16 text-xs text-center">
            <div class="border-t border-black pt-2">
                <p class="font-bold text-slate-800">Dispatched By (Store Keeper)</p>
                <p class="text-slate-500 text-[10px]">Sign & Date</p>
            </div>
            <div class="border-t border-black pt-2">
                <p class="font-bold text-slate-800">Authorized By (Manager)</p>
                <p class="text-slate-500 text-[10px]">Sign & Date</p>
            </div>
        </div>
    </div>
</div>
@endsection
