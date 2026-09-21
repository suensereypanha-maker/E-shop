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
