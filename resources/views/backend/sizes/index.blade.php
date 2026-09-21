@extends('backend.layouts.app', ['title' => 'Sizes Management'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        /* Horizontal spacing and padding for Show entries */
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
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 6px center !important;
            background-repeat: no-repeat !important;
            background-size: 14px 14px !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            outline: none !important;
            cursor: pointer !important;
            transition: all 0.2s;
        }

        div.dataTables_wrapper div.dataTables_length select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important;
        }

        /* Pagination: No background, no border, show only numbers */
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
            button {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6">

        <!-- Header & Add Button -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-700 font-semibold">Sizes</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sizes Management</h1>
            </div>

            <button type="button" onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i>
                <span>Add New Size</span>
            </button>
        </div>

        <!-- Flash Notification -->
        @if (session('success'))
            <div
                class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-emerald-500 hover:text-emerald-700">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- DataTable Card -->
        <div class="">
            <div class="table-responsive">
                {!! $dataTable->table(['class' => 'display', 'style' => 'width:100%']) !!}
            </div>
        </div>

    </div>

    <!-- Modal: Add New Size -->
    <div id="addModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden">
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 w-full max-w-md p-6 relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-base font-bold text-slate-900">Add New Size</h3>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <form action="{{ route('backend.size.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Size Name <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Small, Medium, XL"
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Size Code</label>
                    <input type="text" name="code" placeholder="e.g. S, M, L, 42"
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="status" id="add_status" value="1" checked
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="add_status" class="text-xs text-slate-700 font-medium">Active</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs">
                        Save Size
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Size -->
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden">
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 w-full max-w-md p-6 relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-base font-bold text-slate-900">Edit Size</h3>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Size Name <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Size Code</label>
                    <input type="text" name="code" id="edit_code"
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="status" id="edit_status" value="1"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="edit_status" class="text-xs text-slate-700 font-medium">Active</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs">
                        Update Size
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}

    <script>
        function openEditModal(size) {
            document.getElementById('edit_name').value = size.name || '';
            document.getElementById('edit_code').value = size.code || '';
            document.getElementById('edit_status').checked = (size.status == 1 || size.status === true);

            // Set form action dynamically: /backend/sizes/{id}
            var baseUrl = "{{ url('backend/sizes') }}";
            document.getElementById('editForm').action = baseUrl + '/' + size.id;

            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
@endpush
