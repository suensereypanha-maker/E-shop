<header class="h-16 bg-white/95 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-4 lg:px-8 z-30 sticky top-0 shadow-xs">
    <!-- Left: Mobile Toggle & Page Title / Store Status -->
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100 transition-colors">
            <i class="bi bi-list text-xl"></i>
        </button>

        <div class="flex items-center gap-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="hidden sm:inline">Store:</span> Phnom Penh Main (Open)
            </div>

            <span class="hidden md:inline-block text-slate-300">|</span>

            <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-600">
                <i class="bi bi-clock-history text-slate-400"></i>
                <span>
                    Shift #12:
                    <strong class="text-slate-800">Active</strong>
                    ({{ now()->format('d/m/Y H:i') }})
                </span>
            </div>
        </div>
    </div>

    <!-- Right: Quick Actions & Profile -->
    <div class="flex items-center gap-2.5 sm:gap-3">
        <!-- Quick POS New Sale Button -->
        {{-- <a href="#pos-new-sale"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-all">
            <i class="bi bi-plus-circle text-xs"></i>
            <span>New Sale</span>
        </a>

        <!-- Quick Cash In -->
        <a href="#pos-cash-in"
           class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium shadow-2xs transition-colors">
            <i class="bi bi-box-arrow-in-down text-emerald-600 text-xs"></i>
            <span>Cash In</span>
        </a> --}}

        <!-- Notifications with Badge 3 -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                    class="relative p-2   hover:bg-slate-50 text-slate-600 hover:text-slate-900 transition-colors shadow-2xs">
                <i class="bi bi-bell text-sm"></i>
                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white">
                    3
                </span>
            </button>

            <!-- Notifications Dropdown -->
            <div x-show="open"
                 @click.away="open = false"
                 x-transition
                 class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50 text-xs"
                 style="display: none;">
                <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                    <span class="font-semibold text-slate-800">Notifications</span>
                    <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 text-[10px] font-mono font-bold">3 Unread</span>
                </div>
                <div class="divide-y divide-slate-100 max-h-64 overflow-y-auto">
                    <div class="px-4 py-2.5 hover:bg-slate-50 transition-colors">
                        <p class="text-slate-800 font-medium">New Online Order received</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Order #ORD-8920 ($120.00)</p>
                    </div>
                    <div class="px-4 py-2.5 hover:bg-slate-50 transition-colors">
                        <p class="text-amber-700 font-medium">Low Stock Alert</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">9 products reached minimum threshold</p>
                    </div>
                    <div class="px-4 py-2.5 hover:bg-slate-50 transition-colors">
                        <p class="text-slate-800 font-medium">Quotation pending approval</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Wholesale customer submitted QT-104</p>
                    </div>
                </div>
                <a href="#notifications" class="block text-center py-2 border-t border-slate-100 text-indigo-600 hover:text-indigo-700 font-medium">
                    View all notifications
                </a>
            </div>
        </div>

        @php
            $currentUser = Auth::user();
            $roleBadge = $currentUser ? $currentUser->role_badge : [
                'title' => 'Guest User',
                'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-400',
                'icon' => 'bi-person',
            ];
            $roleTitle = $currentUser ? $currentUser->role_title : 'Guest User';
        @endphp

        <!-- User Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-all">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center font-bold text-white text-xs shadow-sm">
                    {{ strtoupper(substr($currentUser->name ?? 'A', 0, 1)) }}
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-xs font-semibold text-slate-800 leading-tight">{{ $currentUser->name ?? 'Administrator' }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $roleBadge['dot'] }}"></span>
                        <p class="text-[10px] font-medium text-slate-500 leading-none">{{ $roleTitle }}</p>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-[10px] text-slate-400"></i>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open"
                 @click.away="open = false"
                 x-transition
                 class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50 text-xs"
                 style="display: none;">
                <div class="px-4 py-2.5 border-b border-slate-100">
                    <p class="font-medium text-slate-800">{{ $currentUser->name ?? 'Administrator' }}</p>
                    <p class="text-[11px] text-slate-500 truncate mb-1.5">{{ $currentUser->email ?? 'admin@example.com' }}</p>
                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $roleBadge['bg'] }}">
                        <i class="bi {{ $roleBadge['icon'] }}"></i>
                        <span>{{ $roleTitle }}</span>
                    </div>
                </div>

                <a href="#settings-general" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                    <i class="bi bi-gear text-slate-400"></i>
                    <span>Settings</span>
                </a>

                @if($currentUser && $currentUser->isAdmin())
                    <a href="#roles-permissions-section" class="flex items-center justify-between px-4 py-2 text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-shield-lock text-slate-400"></i>
                            <span>Roles & Permissions</span>
                        </div>
                        <span class="text-[9px] font-bold bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded border border-indigo-100 uppercase">Admin</span>
                    </a>
                @endif

                <a href="{{ route('backend.clear-cache') }}"
                   onclick="this.querySelector('i').classList.add('animate-spin')"
                   class="flex items-center gap-2 px-4 py-2 text-amber-600 hover:bg-amber-50 transition-colors">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Clear cache</span>
                </a>

                {{-- <a href="{{ route('backend.role.json') }}" target="_blank" class="flex items-center gap-2 px-4 py-2 text-indigo-600 hover:bg-indigo-50">
                    <i class="bi bi-filetype-json"></i>
                    <span>Export Role JSON</span>
                </a> --}}

                <div class="border-t border-slate-100 my-1"></div>

                @if(Auth::check())
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-rose-600 hover:bg-rose-50 text-left">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-600 hover:bg-indigo-50">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Sign In</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>
