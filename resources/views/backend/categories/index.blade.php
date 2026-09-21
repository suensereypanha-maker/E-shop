@extends('backend.layouts.app', ['title' => 'Categories Management'])

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
                    <span class="text-slate-700 font-semibold">Categories</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Categories Management</h1>
            </div>
            <button type="button" onclick="openAddModal()"
                class="px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i>
                <span>Add New Category</span>
            </button>
        </div>

        <!-- Flash Messages -->
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
        <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-xs">
            <div class="table-responsive">
                {!! $dataTable->table(['class' => 'display', 'style' => 'width:100%']) !!}
            </div>
        </div>
    </div>

    <!-- Modal: Add New Category -->
    <div id="addModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden">
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-base font-bold text-slate-900">Add New Category</h3>
                <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <form action="{{ route('backend.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Category Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Men's Fashion, Electronics, Footwear"
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Slug (optional)</label>
                    <input type="text" name="slug" placeholder="Auto-generated if empty"
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Parent Category</label>
                    <select name="parent_id" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">None (Top Level / Root)</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Category Image</label>
                    <div class="flex items-center gap-3">
                        <div id="add_image_preview_wrapper" class="w-12 h-12 rounded border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden hidden">
                            <img id="add_image_preview" src="" alt="Preview" class="w-full h-full object-cover">
                        </div>
                        <input type="file" name="image" accept="image/*" onchange="previewImage(this, 'add_image_preview', 'add_image_preview_wrapper')"
                            class="flex-1 text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Brief description of the category..."
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="status" id="add_status" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="add_status" class="text-xs text-slate-700 font-medium">Active</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeAddModal()"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Category -->
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden">
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-base font-bold text-slate-900">Edit Category</h3>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Category Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Slug</label>
                    <input type="text" name="slug" id="edit_slug"
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Parent Category</label>
                    <select name="parent_id" id="edit_parent_id" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">None (Top Level / Root)</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" class="edit-parent-opt-{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Category Image</label>
                    <div class="flex items-center gap-3">
                        <div id="edit_image_preview_wrapper" class="w-12 h-12 rounded border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden hidden">
                            <img id="edit_image_preview" src="" alt="Category Image" class="w-full h-full object-cover">
                        </div>
                        <input type="file" name="image" accept="image/*" onchange="previewImage(this, 'edit_image_preview', 'edit_image_preview_wrapper')"
                            class="flex-1 text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="status" id="edit_status" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="edit_status" class="text-xs text-slate-700 font-medium">Active</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs">Update Category</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function openEditModal(category) {
            document.getElementById('edit_name').value = category.name || '';
            document.getElementById('edit_slug').value = category.slug || '';
            document.getElementById('edit_description').value = category.description || '';
            document.getElementById('edit_status').checked = (category.status == 1 || category.status === true);

            // Handle parent dropdown: enable all options, then hide/disable own category
            var parentSelect = document.getElementById('edit_parent_id');
            var options = parentSelect.querySelectorAll('option');
            options.forEach(function(opt) {
                opt.disabled = false;
                opt.hidden = false;
                if (opt.value && parseInt(opt.value) === parseInt(category.id)) {
                    opt.disabled = true;
                    opt.hidden = true;
                }
            });

            parentSelect.value = category.parent_id || '';

            // Handle Image Preview
            var previewWrapper = document.getElementById('edit_image_preview_wrapper');
            var previewImg = document.getElementById('edit_image_preview');
            if (category.image) {
                previewImg.src = '{{ asset("") }}' + category.image;
                previewWrapper.classList.remove('hidden');
            } else {
                previewImg.src = '';
                previewWrapper.classList.add('hidden');
            }

            var baseUrl = "{{ url('backend/categories') }}";
            document.getElementById('editForm').action = baseUrl + '/' + category.id;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function previewImage(input, previewId, wrapperId) {
            var file = input.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById(previewId);
                    preview.src = e.target.result;
                    document.getElementById(wrapperId).classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
@endpush
