<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'E-Shop' }} - Management Dashboard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">


    <!-- Tailwind CSS CDN for instant robust styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Kantumruy Pro"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    <!-- App Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Alpine.js & Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Kantumruy Pro', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }

        /* Clean Light Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .sidebar-item-active {
            background-color: #eef2ff;
            border-left: 3px solid #4f46e5;
            color: #4338ca !important;
            font-weight: 600;
        }

        /* Clean Length Selector for DataTables */
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

        /* Clean Pagination: No background, no border, numbers only */
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

        /* Enforce simple, clean solid black bordered table style for all DataTables */
        .table-black,
        .table-black th,
        .table-black td,
        table.dataTable,
        table.dataTable thead th,
        table.dataTable thead td,
        table.dataTable tbody td,
        table.dataTable tfoot th,
        table.dataTable tfoot td {
            border: 1px solid #000000 !important;
            border-collapse: collapse !important;
        }

        table.dataTable {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #000000 !important;
        }

        table.dataTable thead th,
        table.dataTable thead td {
            text-align: left !important;
            padding: 8px 12px !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
            color: #000000 !important;
            border: 1px solid #000000 !important;
            background-color: #f1f5f9 !important;
        }

        table.dataTable tbody td {
            text-align: left !important;
            vertical-align: middle !important;
            padding: 8px 12px !important;
            font-size: 12px !important;
            border: 1px solid #000000 !important;
            background-color: #ffffff !important;
        }

        table.dataTable tbody tr:hover,
        table.dataTable.hover tbody tr:hover,
        table.dataTable.display tbody tr:hover {
            background-color: #f8fafc !important;
        }

        table.dataTable tfoot th,
        table.dataTable tfoot td {
            text-align: left !important;
            padding: 8px 12px !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            color: #000000 !important;
            border: 1px solid #000000 !important;
            background-color: #f1f5f9 !important;
        }

        /* Global Print Rules: ensure full pagination across all pages without scrollbar clipping */
        @media print {
            @page {
                size: auto;
                margin: 0mm;
            }

            html,
            body {
                height: auto !important;
                min-height: 100% !important;
                overflow: visible !important;
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print,
            header,
            aside,
            nav,
            footer {
                display: none !important;
            }

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
            button {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen overflow-x-hidden"
      x-data="{
          sidebarOpen: false,
          lang: localStorage.getItem('eshop_lang') || 'en',
          menuSearch: '',
          openMenus: {},
          setLang(l) {
              this.lang = l;
              localStorage.setItem('eshop_lang', l);
          },
          toggleMenu(id) {
              this.openMenus[id] = !this.openMenus[id];
          }
      }">

    <div class="flex h-screen overflow-hidden bg-slate-50">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar Component -->
        @include('backend.layouts.sidebar', ['menus' => (!empty($menus) ? $menus : app(\App\Services\MenuService::class)->getMenuTree())])

        <!-- Main Wrapper -->
        <div class="flex flex-col flex-1 w-0 overflow-hidden bg-slate-50">
            <!-- Navbar Component -->
            @include('backend.layouts.navbar')

            <!-- Main Content Area -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none p-4 md:p-6 lg:p-8 bg-slate-50">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables Core & Buttons -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    @stack('scripts')
</body>
</html>
