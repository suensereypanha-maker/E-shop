@extends('backend.layouts.app', ['title' => 'Stock In Management'])

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
                    <span class="text-slate-700 font-semibold">Stock In</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Stock In Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">Receive inventory from suppliers, record purchase costs, and update stock levels.</p>
            </div>
            <a href="{{ route('backend.stock-ins.create') }}"
                class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm hover:shadow transition flex items-center gap-2 self-start sm:self-auto">
                <i class="bi bi-card-checklist text-base"></i>
                <span>Multi-Item Invoice</span>
            </a>
        </div>

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="text-xs font-medium text-slate-500">Total Stock-In Records</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total_transactions']) }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-boxes"></i>
                </div>
                <div>
                    <div class="text-xs font-medium text-slate-500">Total Quantity Received</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total_received_qty']) }} <span class="text-xs font-normal text-slate-400"></span></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="text-xs font-medium text-slate-500">Total Purchase Valuation</div>
                    <div class="text-xl font-bold text-emerald-700 mt-0.5 font-mono">${{ number_format($stats['total_received_cost'], 2) }}</div>
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

        <!-- DataTable Card with Dual-Tab Quick Entry (Stock In & New Product) -->
        <div class="mb-6">

            <!-- 1. Quick Entry Panel (Includes Quick Stock In & Quick Product Creator) -->
            <div class="mb-6" id="quickEntryPanel">
                <!-- Panel Header with Interactive Tabs -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3.5 pb-2.5 border-b border-slate-200/80">
                    <div class="flex items-center gap-1.5 p-1 bg-slate-200/60 rounded-lg w-fit">
                        <button type="button" id="tabBtnStockIn" onclick="switchQuickTab('stock_in')"
                            class="px-3 py-1.5 rounded-md text-xs font-bold transition-all shadow-xs bg-white text-indigo-700 flex items-center gap-1.5">
                            <i class="bi bi-box-arrow-in-down text-indigo-600"></i>
                            <span>Quick Stock In</span>
                        </button>
                        <button type="button" id="tabBtnNewProduct" onclick="switchQuickTab('new_product')"
                            class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all text-slate-600 hover:text-slate-900 hover:bg-white/60 flex items-center gap-1.5">
                            <i class="bi bi-plus-circle text-emerald-600"></i>
                            <span>+ Input New Product</span>
                        </button>
                    </div>

                   
                </div>

                <!-- TAB A: QUICK STOCK IN FORM -->
                <div id="tabContentStockIn">
                    <form action="{{ route('backend.stock-ins.store') }}" method="POST" id="quickStockInForm">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-end">
                            <!-- 1. Supplier (2 cols) -->
                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    Supplier <span class="text-slate-400 font-normal">(Opt)</span>
                                </label>
                                <select name="supplier_id" id="quick_supplier_id"
                                    class="w-full text-xs px-2.5 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white">
                                    <option value="">-- Direct / None --</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Product (3 cols) with "+ New Product" quick trigger -->
                            <div class="lg:col-span-3">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-semibold text-slate-700">
                                        Product <span class="text-rose-500">*</span>
                                    </label>
                                    <button type="button" onclick="switchQuickTab('new_product')"
                                        class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-0.5" title="Input a new product into catalog">
                                        <i class="bi bi-plus-lg"></i> New Product
                                    </button>
                                </div>
                                <select name="items[0][product_id]" id="quick_product_id" required
                                    onchange="onQuickProductChange(this.value)"
                                    class="w-full text-xs px-2.5 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white font-medium">
                                    <option value="">-- Choose Product --</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}">{{ $p->name }} </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 3. Variation: Color / Size (3 cols) -->
                            <div class="lg:col-span-3">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-semibold text-slate-700">
                                        Variation <span class="text-rose-500">*</span>
                                    </label>
                                    <span id="quickStockBadge" class="text-[10px] font-mono text-slate-400">Stock: -</span>
                                </div>
                                <select name="items[0][variation_id]" id="quick_variation_id" required disabled
                                    onchange="onQuickVariationChange(this.value)"
                                    class="w-full text-xs px-2.5 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white">
                                    <option value="">-- Select Product First --</option>
                                </select>
                            </div>

                            <!-- 4. Quantity (1.5 cols) -->
                            <div class="lg:col-span-1">
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    Qty <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="items[0][quantity]" id="quick_quantity" value="1" min="1" step="1" required
                                    oninput="updateQuickSubtotal()"
                                    class="w-full text-xs font-bold text-center px-2 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white text-slate-900">
                            </div>

                            <!-- 5. Unit Cost (1.5 cols) -->
                            <div class="lg:col-span-1">
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    Cost ($) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="items[0][unit_cost]" id="quick_unit_cost" value="0.00" min="0" step="0.01" required
                                    oninput="updateQuickSubtotal()"
                                    class="w-full text-xs font-mono text-right px-2 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white">
                            </div>

                            <!-- 6. Submit Button (1.5 cols) -->
                            <div class="lg:col-span-2">
                                <button type="submit" id="quickSubmitBtn"
                                    class="w-full py-2 px-3 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center justify-center gap-1.5">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                    <span>Stock In</span>
                                </button>
                            </div>
                        </div>

                        <!-- Extra Details Bar -->
                        <div class="mt-3 pt-2.5 border-t border-slate-200/70 flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-500">
                            <div class="flex flex-wrap items-center gap-4">
                               
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-slate-600">Date:</span>
                                    <input type="date" name="received_date" value="{{ date('Y-m-d') }}" required
                                        class="text-xs px-2.5 py-1 rounded-md border border-slate-300 focus:border-indigo-500 outline-none bg-white">
                                </div>
                            </div>

                            <!-- Live Stock Preview Badge -->
                            <div id="quickStockPreview" class="hidden items-center gap-2 font-mono text-xs">
                                <span class="text-slate-400">Stock Impact:</span>
                                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-semibold" id="prevStockBefore">0</span>
                                <i class="bi bi-arrow-right text-indigo-500"></i>
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold" id="prevStockAfter">0</span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB B: QUICK PRODUCT CREATOR FORM -->
                <div id="tabContentNewProduct" class="hidden mb-6">
                    <form id="quickProductForm" onsubmit="submitQuickProduct(event)" enctype="multipart/form-data">
                        @csrf
                        <div class=" mb-2">
                            <!-- Row 1: Name, Category, Brand, Product Image -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                                <!-- Product Name (4 cols) -->
                                <div class="lg:col-span-4">
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                        Product Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="np_name" required placeholder="e.g. Vintage Denim Jacket"
                                        class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                                </div>

                                <!-- Category (3 cols) -->
                                <div class="lg:col-span-3">
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Category</label>
                                    <select name="category_id" id="np_category" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none bg-white">
                                        <option value="">-- No Category --</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Brand (2 cols) -->
                                <div class="lg:col-span-2">
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Brand</label>
                                    <select name="brand_id" id="np_brand" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none bg-white">
                                        <option value="">-- No Brand --</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Product Image Upload (3 cols) -->
                                <div class="lg:col-span-3">
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Product Image</label>
                                    <div class="flex items-center gap-2">
                                        <div id="np_img_preview" class="w-8 h-8 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center text-slate-400 overflow-hidden shrink-0">
                                            <i class="bi bi-image text-sm"></i>
                                        </div>
                                        <input type="file" name="image" id="np_image" accept="image/*" onchange="previewNewProductImage(this)"
                                            class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: 4 Pricing Fields (Cost, Base, Sale, Wholesale) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <!-- Cost Price -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                        Cost Price ($) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="number" name="cost_price" id="np_cost_price" required min="0" step="0.01" value="0.00"
                                        class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none font-mono">
                                </div>

                                <!-- Base Selling Price -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                        Base Selling Price ($)
                                    </label>
                                    <input type="number" name="base_price" id="np_base_price" min="0" step="0.01" placeholder="Optional"
                                        class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none font-mono">
                                </div>

                                <!-- Sale Price -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Sale Price ($)</label>
                                    <input type="number" name="sale_price" id="np_sale_price" min="0" step="0.01" placeholder="Optional"
                                        class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none font-mono">
                                </div>

                                <!-- Wholesale Price -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Wholesale Price ($)</label>
                                    <input type="number" name="wholesale_price" id="np_wholesale_price" min="0" step="0.01" placeholder="Optional"
                                        class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none font-mono">
                                </div>
                            </div>

                            <!-- Row 3: 1 Color (with color_price), Many Sizes (with size_price) & SKU -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 pt-2.5 border-t border-slate-100">
                                <!-- 1. Color (1 Color) + Color Price (4 cols) -->
                                <div class="lg:col-span-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-semibold text-slate-700">
                                            Color <span class="text-slate-400 font-normal">(1 Color)</span>
                                        </label>
                                        <span class="text-[10px] text-slate-400">Color Price ($)</span>
                                    </div>
                                    <div class="grid grid-cols-12 gap-1.5">
                                        <div class="col-span-7 sm:col-span-8">
                                            <select name="color_id" id="np_color_id"
                                                class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none bg-white">
                                                <option value="">-- No Color / Standard --</option>
                                                @foreach ($colors as $color)
                                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-5 sm:col-span-4">
                                            <input type="number" name="color_price" id="np_color_price" min="0" step="0.01" value="0.00" placeholder="0.00"
                                                title="Extra price for this color"
                                                class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none font-mono text-right bg-white">
                                        </div>
                                    </div>
                                    <div class="mt-2 min-h-[26px] flex items-center">
                                        <span class="text-[11px] text-slate-400 italic">Select 1 color and optional extra price</span>
                                    </div>
                                </div>

                                <!-- 2. Sizes (Many Sizes with Size Price) (5 cols) -->
                                <div class="lg:col-span-5">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-semibold text-slate-700">
                                            Sizes <span class="text-slate-400 font-normal">(Add multiple sizes)</span>
                                        </label>
                                        <span id="selectedSizesCount" class="text-[10px] text-slate-400">0 selected</span>
                                    </div>
                                    <select id="np_size_dropdown" onchange="onSizeDropdownSelect(this)"
                                        class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none bg-white">
                                        <option value="">-- Choose Size to Add --</option>
                                        @foreach ($sizes as $size)
                                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                                        @endforeach
                                    </select>
                                    <!-- Badges container for added sizes with individual size_price -->
                                    <div id="selectedSizesBadges" class="flex flex-wrap gap-1.5 mt-2 min-h-[26px]">
                                        <span class="text-[11px] text-slate-400 italic" id="emptySizeHint">No sizes chosen (uses Standard)</span>
                                    </div>
                                </div>

                                <!-- 3. SKU / Code (3 cols) -->
                                <div class="lg:col-span-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-semibold text-slate-700">
                                            SKU / Code <span class="text-slate-400 font-normal">(Optional)</span>
                                        </label>
                                        <span class="text-[10px] text-slate-400">Auto if blank</span>
                                    </div>
                                    <input type="text" name="sku" id="np_sku" placeholder="Auto-generated if blank"
                                        class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none font-mono bg-white">
                                    <div class="mt-2 min-h-[26px] flex items-center">
                                        <span class="text-[11px] text-slate-400 italic">e.g. PRD-ITEM (Variations append size)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Submission Footer -->
                        <div class="flex items-center justify-between gap-3 pt-1">
                            {{-- <span class="text-[11px] text-slate-400 italic">
                                * Variations will be generated automatically based on selected colors and sizes.
                            </span> --}}

                            <div class="flex items-center gap-2">
                                <button type="button" onclick="switchQuickTab('stock_in')"
                                    class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 text-xs font-semibold hover:bg-slate-50 transition">
                                    Cancel
                                </button>
                                <button type="submit" id="npSubmitBtn"
                                    class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Create Product & Select for Stock In</span>
                                </button>
                            </div>
                        </div>
                </div>
            </div>

            <!-- 2. DataTable Component -->
            <div class="">
                {!! $dataTable->table(['class' => 'display', 'style' => 'width:100%']) !!}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}

    <script>
        let quickProductsData = @json($products);

        // Tab Switching Logic
        function switchQuickTab(tab) {
            const btnStockIn = document.getElementById('tabBtnStockIn');
            const btnNewProduct = document.getElementById('tabBtnNewProduct');
            const contentStockIn = document.getElementById('tabContentStockIn');
            const contentNewProduct = document.getElementById('tabContentNewProduct');
            const scannerBox = document.getElementById('barcodeScannerBox');

            if (tab === 'stock_in') {
                if (btnStockIn) btnStockIn.className = 'px-3 py-1.5 rounded-md text-xs font-bold transition-all shadow-xs bg-white text-indigo-700 flex items-center gap-1.5';
                if (btnNewProduct) btnNewProduct.className = 'px-3 py-1.5 rounded-md text-xs font-semibold transition-all text-slate-600 hover:text-slate-900 hover:bg-white/60 flex items-center gap-1.5';
                if (contentStockIn) contentStockIn.classList.remove('hidden');
                if (contentNewProduct) contentNewProduct.classList.add('hidden');
                if (scannerBox) scannerBox.classList.remove('hidden');
            } else {
                if (btnNewProduct) btnNewProduct.className = 'px-3 py-1.5 rounded-md text-xs font-bold transition-all shadow-xs bg-white text-emerald-700 flex items-center gap-1.5';
                if (btnStockIn) btnStockIn.className = 'px-3 py-1.5 rounded-md text-xs font-semibold transition-all text-slate-600 hover:text-slate-900 hover:bg-white/60 flex items-center gap-1.5';
                if (contentNewProduct) contentNewProduct.classList.remove('hidden');
                if (contentStockIn) contentStockIn.classList.add('hidden');
                if (scannerBox) scannerBox.classList.add('hidden');
                setTimeout(() => {
                    const npName = document.getElementById('np_name');
                    if (npName) npName.focus();
                }, 100);
            }
        }

        // Quick Product AJAX Creation
        function submitQuickProduct(e) {
            e.preventDefault();
            const btn = document.getElementById('npSubmitBtn');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Saving...';
            }

            const form = document.getElementById('quickProductForm');
            const formData = new FormData(form);

            fetch("{{ route('backend.products.store') }}", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(res => {
                return res.json().then(data => {
                    return { ok: res.ok, status: res.status, data: data };
                }).catch(() => {
                    return { ok: res.ok, status: res.status, data: { status: 'error', message: 'HTTP ' + res.status + ' response' } };
                });
            })
            .then(({ ok, status, data }) => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }

                if (ok && data.status === 'success') {
                    // 1. Add product to client array
                    if (data.product) {
                        quickProductsData.push(data.product);

                        // 2. Append option to product dropdown
                        const prodSelect = document.getElementById('quick_product_id');
                        if (prodSelect) {
                            const opt = document.createElement('option');
                            opt.value = data.product.id;
                            opt.textContent = data.product.name;
                            opt.dataset.cost = data.product.cost_price;
                            prodSelect.appendChild(opt);

                            // 3. Select the new product
                            prodSelect.value = data.product.id;
                        }

                        // 4. Switch back to Stock In tab and trigger variation load
                        switchQuickTab('stock_in');
                        onQuickProductChange(data.product.id);
                    }

                    // 5. Reset product form & UI elements
                    form.reset();
                    selectedSizesList = [];
                    renderSizeBadges();
                    const preview = document.getElementById('np_img_preview');
                    if (preview) preview.innerHTML = '<i class="bi bi-image text-sm"></i>';

                    // 6. Show green alert
                    showAjaxAlert(data.message || 'Product created successfully!', 'success');

                    // 7. Focus quantity input
                    setTimeout(() => {
                        const qtyInput = document.getElementById('quick_quantity');
                        if (qtyInput) qtyInput.focus();
                    }, 150);
                } else {
                    let errMsg = data.message || 'Error creating product';
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey].length > 0) {
                            errMsg = data.errors[firstKey][0];
                        }
                    }
                    showAjaxAlert(errMsg, 'error');
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                showAjaxAlert(err.message || 'Something went wrong. Please check your inputs.', 'error');
            });
        }

        // Image Preview Handler
        function previewNewProductImage(input) {
            const preview = document.getElementById('np_img_preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-lg">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '<i class="bi bi-image text-sm"></i>';
            }
        }

        // Size Dropdown Multi-Tagging Logic with Individual Size Price
        let selectedSizesList = [];
        function onSizeDropdownSelect(select) {
            const sizeId = select.value;
            const sizeName = select.options[select.selectedIndex].text;
            if (!sizeId) return;

            if (!selectedSizesList.some(s => s.id == sizeId)) {
                selectedSizesList.push({ id: sizeId, name: sizeName, price: '0.00' });
                renderSizeBadges();
            }
            select.value = '';
        }

        function removeSelectedSize(sizeId) {
            selectedSizesList = selectedSizesList.filter(s => s.id != sizeId);
            renderSizeBadges();
        }

        function updateSizePrice(sizeId, val) {
            const item = selectedSizesList.find(s => s.id == sizeId);
            if (item) {
                item.price = val;
            }
        }

        function renderSizeBadges() {
            const container = document.getElementById('selectedSizesBadges');
            const countSpan = document.getElementById('selectedSizesCount');
            countSpan.textContent = `${selectedSizesList.length} selected`;

            if (selectedSizesList.length === 0) {
                container.innerHTML = '<span class="text-[11px] text-slate-400 italic" id="emptySizeHint">No sizes chosen (uses Standard)</span>';
                return;
            }

            container.innerHTML = selectedSizesList.map(s => `
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 shadow-2xs">
                    <span class="font-semibold">${s.name}</span>
                    <span class="text-[10px] text-emerald-600 font-mono">+$</span>
                    <input type="number" name="sizes[${s.id}][size_price]" value="${s.price || '0.00'}" step="0.01" min="0"
                        oninput="updateSizePrice(${s.id}, this.value)"
                        title="Extra price for size ${s.name}"
                        class="w-14 px-1 py-0.5 text-[11px] font-mono rounded border border-emerald-300 bg-white text-right focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <input type="hidden" name="sizes[${s.id}][id]" value="${s.id}">
                    <button type="button" onclick="removeSelectedSize(${s.id})" class="text-emerald-400 hover:text-rose-600 transition ml-0.5">
                        <i class="bi bi-x-circle-fill text-[11px]"></i>
                    </button>
                </span>
            `).join('');
        }

        function showAjaxAlert(message, type = 'success') {
            const container = document.getElementById('ajaxAlertContainer');
            const isSuccess = type === 'success';
            const bg = isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800';
            const icon = isSuccess ? 'bi-check-circle-fill text-emerald-600' : 'bi-exclamation-triangle-fill text-rose-600';

            container.innerHTML = `
                <div class="p-3.5 rounded-lg ${bg} border text-xs flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <i class="bi ${icon}"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="hover:opacity-75">
                        <i class="bi bi-x-lg text-xs"></i>
                    </button>
                </div>
            `;
        }

        function onQuickProductChange(productId, preselectedVariationId = null) {
            const varSelect = document.getElementById('quick_variation_id');
            const costInput = document.getElementById('quick_unit_cost');
            const stockBadge = document.getElementById('quickStockBadge');
            const previewBox = document.getElementById('quickStockPreview');

            varSelect.innerHTML = '<option value="">-- Choose Variation --</option>';
            stockBadge.textContent = 'Stock: -';
            stockBadge.className = 'text-[10px] font-mono text-slate-400';
            previewBox.classList.add('hidden');
            previewBox.classList.remove('flex');

            if (!productId) {
                varSelect.disabled = true;
                costInput.value = '0.00';
                updateQuickSubtotal();
                return;
            }

            const product = quickProductsData.find(p => p.id == productId);
            if (!product) return;

            // Auto pre-fill product cost_price if available
            if (product.cost_price !== undefined && product.cost_price !== null && product.cost_price !== '') {
                costInput.value = parseFloat(product.cost_price).toFixed(2);
            } else {
                costInput.value = '0.00';
            }

            if (!product.variations || product.variations.length === 0) {
                varSelect.innerHTML = '<option value="">(No variations available)</option>';
                varSelect.disabled = true;
                return;
            }

            varSelect.disabled = false;
            product.variations.forEach(v => {
                const colorName = v.color ? (v.color.name || v.color) : 'Standard';
                const sizeName = v.size ? (v.size.name || v.size) : 'Standard';
                const sku = v.sku || 'No SKU';
                const isSelected = preselectedVariationId && preselectedVariationId == v.id ? 'selected' : '';

                varSelect.innerHTML += `
                    <option value="${v.id}" data-stock="${v.stock}" ${isSelected}>
                        ${colorName} / ${sizeName}
                    </option>
                `;
            });

            // Auto select if only 1 variation or preselected
            if (preselectedVariationId) {
                varSelect.value = preselectedVariationId;
                onQuickVariationChange(preselectedVariationId);
            } else if (product.variations.length === 1) {
                varSelect.value = product.variations[0].id;
                onQuickVariationChange(product.variations[0].id);
            }

            updateQuickSubtotal();
        }

        function onQuickVariationChange(variationId) {
            const varSelect = document.getElementById('quick_variation_id');
            const stockBadge = document.getElementById('quickStockBadge');
            const previewBox = document.getElementById('quickStockPreview');
            const selectedOption = varSelect.options[varSelect.selectedIndex];

            if (selectedOption && selectedOption.dataset.stock !== undefined) {
                const currentStock = parseInt(selectedOption.dataset.stock);
                stockBadge.textContent = `Stock: ${currentStock} `;
                stockBadge.className = currentStock > 0 
                    ? 'text-[10px] font-mono text-emerald-700 font-bold' 
                    : 'text-[10px] font-mono text-rose-600 font-bold';

                // Show preview impact
                const inwardQty = parseInt(document.getElementById('quick_quantity').value) || 0;
                document.getElementById('prevStockBefore').textContent = `${currentStock} `;
                document.getElementById('prevStockAfter').textContent = `${currentStock + inwardQty} `;
                previewBox.classList.remove('hidden');
                previewBox.classList.add('flex');
            } else {
                stockBadge.textContent = 'Stock: -';
                previewBox.classList.add('hidden');
                previewBox.classList.remove('flex');
            }

            updateQuickSubtotal();
        }

        function updateQuickSubtotal() {
            const qtyInput = document.getElementById('quick_quantity');
            const costInput = document.getElementById('quick_unit_cost');
            const qty = qtyInput ? parseFloat(qtyInput.value) || 0 : 0;
            const cost = costInput ? parseFloat(costInput.value) || 0 : 0;
            const subtotal = qty * cost;

            const badge = document.getElementById('quickSubtotalBadge');
            if (badge) {
                if (subtotal > 0) {
                    badge.textContent = `$${subtotal.toFixed(2)}`;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            // Update after stock preview if active
            const varSelect = document.getElementById('quick_variation_id');
            if (varSelect && varSelect.selectedIndex >= 0) {
                const selectedOption = varSelect.options[varSelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.stock !== undefined) {
                    const currentStock = parseInt(selectedOption.dataset.stock);
                    const prevStockAfter = document.getElementById('prevStockAfter');
                    if (prevStockAfter) {
                        prevStockAfter.textContent = `${currentStock + qty} `;
                    }
                }
            }
        }

        // Barcode / SKU Quick Scan listener (safe check if element exists)
        const barcodeInput = document.getElementById('quickBarcodeInput');
        if (barcodeInput) {
            barcodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const skuVal = this.value.trim().toLowerCase();
                    if (!skuVal) return;

                    let foundProd = null;
                    let foundVar = null;

                    for (const p of quickProductsData) {
                        if (p.variations) {
                            const mVar = p.variations.find(v => v.sku && v.sku.toLowerCase() === skuVal);
                            if (mVar) {
                                foundProd = p;
                                foundVar = mVar;
                                break;
                            }
                        }
                    }

                    if (foundProd && foundVar) {
                        const prodSelect = document.getElementById('quick_product_id');
                        if (prodSelect) prodSelect.value = foundProd.id;
                        onQuickProductChange(foundProd.id, foundVar.id);
                        const qtyInput = document.getElementById('quick_quantity');
                        if (qtyInput) qtyInput.focus();
                        this.value = '';
                    } else {
                        alert(`No product variation found with SKU: ${skuVal}`);
                    }
                }
            });
        }
    </script>
@endpush
