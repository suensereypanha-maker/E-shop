<aside class="flex flex-col w-72 bg-white border-r border-slate-200 shrink-0 transition-all duration-300 z-50 fixed inset-y-0 left-0 lg:static lg:translate-x-0 shadow-sm"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">

    <!-- Brand Header -->
    <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200 bg-white">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                <i class="bi bi-shop text-white text-lg"></i>
            </div>
            <div>
                <span class="font-bold text-base tracking-tight text-slate-900 flex items-center gap-1.5">
                    E-SHOP 
                </span>
                <p class="text-[10px] text-slate-500 font-medium leading-none mt-0.5">Management & POS</p>
            </div>
        </a>

        <!-- Mobile close button -->
        <button @click="sidebarOpen = false" class="lg:hidden text-slate-500 hover:text-slate-800 p-1 rounded-lg hover:bg-slate-100">
            <i class="bi bi-x-lg text-base"></i>
        </button>
    </div>

    <!-- Language Switcher Bar -->
    <div class="px-4 pt-3 pb-2 border-b border-slate-100 bg-slate-50/50">
        <div class="flex items-center justify-between text-[11px] mb-1.5 px-1 text-slate-500 font-semibold tracking-wider uppercase">
            <span>Language / ភាសា</span>
            <span class="text-indigo-600 font-mono text-[10px] font-bold" x-text="lang.toUpperCase()"></span>
        </div>
        <div class="grid grid-cols-3 gap-1.5 p-1 bg-white rounded-lg border border-slate-200 shadow-sm">
            <button type="button"
                    @click="setLang('en')"
                    :class="lang === 'en' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="py-1 px-2 rounded-md text-xs transition-all text-center font-medium">
                EN
            </button>
            <button type="button"
                    @click="setLang('kh')"
                    :class="lang === 'kh' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="py-1 px-2 rounded-md text-xs transition-all text-center font-['Kantumruy_Pro'] font-medium">
                ខ្មែរ
            </button>
            <button type="button"
                    @click="setLang('zh')"
                    :class="lang === 'zh' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="py-1 px-2 rounded-md text-xs transition-all text-center font-medium">
                中文
            </button>
        </div>
    </div>

    <!-- Quick Search Menu Items -->
    <div class="px-4 py-2.5">
        <div class="relative">
            <i class="bi bi-search absolute left-3 top-2.5 text-xs text-slate-400"></i>
            <input type="text"
                   x-model="menuSearch"
                   placeholder="Search menus & submenus..."
                   class="w-full bg-slate-50 text-slate-800 text-xs pl-8 pr-7 py-2 rounded-lg border border-slate-200 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all">
            <button x-show="menuSearch" @click="menuSearch = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
    </div>

    <!-- Navigation Menu List -->
    <div class="flex-1 overflow-y-auto px-3 py-2 space-y-1">
        @foreach($menus as $menu)
            @php
                $hasChildren = is_object($menu) ? $menu->hasChildren() : !empty($menu['children']);
                $menuId = is_object($menu) ? $menu->id : ($menu['sort'] ?? $loop->iteration);
                $menuName = is_object($menu) ? $menu->name : $menu['name'];
                $menuIcon = is_object($menu) ? $menu->icon : ($menu['icon'] ?? 'bi-circle');
                $menuBadge = is_object($menu) ? $menu->badge : ($menu['badge'] ?? null);
                $menuRoute = is_object($menu) ? $menu->route : ($menu['route'] ?? null);

                $transEn = is_object($menu) ? $menu->getTranslatedName('en') : ($menu['translations']['en']['name'] ?? $menuName);
                $transKh = is_object($menu) ? $menu->getTranslatedName('kh') : ($menu['translations']['kh']['name'] ?? $menuName);
                $transZh = is_object($menu) ? $menu->getTranslatedName('zh') : ($menu['translations']['zh']['name'] ?? $menuName);

                $children = is_object($menu) ? $menu->children : ($menu['children'] ?? []);

                // Target URL calculation
                $targetUrl = '#';
                if ($menuRoute) {
                    if (str_starts_with($menuRoute, '#')) {
                        $targetUrl = $menuRoute;
                    } elseif (\Illuminate\Support\Facades\Route::has($menuRoute)) {
                        $targetUrl = route($menuRoute);
                    } else {
                        $targetUrl = url($menuRoute);
                    }
                }

                // Collect comprehensive searchable keywords (parent + all children in en, kh, zh)
                $keywords = [
                    strtolower($menuName),
                    strtolower($transEn),
                    strtolower($transKh),
                    strtolower($transZh),
                ];

                foreach ($children as $c) {
                    $cName = is_object($c) ? $c->name : $c['name'];
                    $cEn = is_object($c) ? $c->getTranslatedName('en') : ($c['translations']['en']['name'] ?? $cName);
                    $cKh = is_object($c) ? $c->getTranslatedName('kh') : ($c['translations']['kh']['name'] ?? $cName);
                    $cZh = is_object($c) ? $c->getTranslatedName('zh') : ($c['translations']['zh']['name'] ?? $cName);

                    $keywords[] = strtolower($cName);
                    $keywords[] = strtolower($cEn);
                    $keywords[] = strtolower($cKh);
                    $keywords[] = strtolower($cZh);
                }

                $keywordsJson = json_encode(array_values(array_unique(array_filter($keywords))), JSON_UNESCAPED_UNICODE);
            @endphp

            <div class="menu-group"
                 x-data="{
                     id: '{{ $menuId }}',
                     keywords: {{ $keywordsJson }},
                     matchesSearch() {
                         if (!menuSearch || !menuSearch.trim()) return true;
                         let q = menuSearch.toLowerCase().trim();
                         return this.keywords.some(k => k && k.includes(q));
                     }
                 }"
                 x-show="matchesSearch()">

                @if(!$hasChildren)
                    <!-- Direct Link (No Children) -->
                    <a href="{{ $targetUrl }}"
                       class="flex items-center justify-between px-3 py-2 rounded-lg text-[13px] font-medium transition-all group {{ request()->routeIs('dashboard') && $menuRoute === 'dashboard' ? 'sidebar-item-active' : 'text-slate-700 hover:text-indigo-600 hover:bg-slate-100/80' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <i class="bi {{ $menuIcon }} text-base text-slate-500 group-hover:text-indigo-600 transition-colors shrink-0"></i>
                            <span class="truncate"
                                  x-text="lang === 'kh' ? '{{ addslashes($transKh) }}' : (lang === 'zh' ? '{{ addslashes($transZh) }}' : '{{ addslashes($transEn) }}')">
                                {{ $menuName }}
                            </span>
                        </div>

                        @if($menuBadge)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $menuBadge }}
                            </span>
                        @endif
                    </a>
                @else
                    <!-- Collapsible Dropdown (Has Children) -->
                    @php
                        $hasActiveChild = false;
                        foreach ($children as $tempChild) {
                            $tRoute = is_object($tempChild) ? $tempChild->route : ($tempChild['route'] ?? null);
                            if ($tRoute) {
                                if (\Illuminate\Support\Facades\Route::has($tRoute) && request()->routeIs($tRoute, $tRoute . '.*')) {
                                    $hasActiveChild = true;
                                    break;
                                }
                                if (($tRoute === 'backend.size.index' || $tRoute === 'backend.sizes.index') && request()->routeIs('backend.size.*', 'backend.sizes.*')) {
                                    $hasActiveChild = true;
                                    break;
                                }
                            }
                        }
                    @endphp
                    <div x-init="if ({{ $hasActiveChild ? 'true' : 'false' }}) { openMenus['{{ $menuId }}'] = true }">
                        <button type="button"
                                @click="toggleMenu('{{ $menuId }}')"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-[13px] font-medium transition-all group {{ $hasActiveChild ? 'text-indigo-700 font-semibold bg-indigo-50/50' : 'text-slate-700 hover:text-indigo-600 hover:bg-slate-100/80' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <i class="bi {{ $menuIcon }} text-base {{ $hasActiveChild ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }} transition-colors shrink-0"></i>
                                <span class="truncate"
                                      x-text="lang === 'kh' ? '{{ addslashes($transKh) }}' : (lang === 'zh' ? '{{ addslashes($transZh) }}' : '{{ addslashes($transEn) }}')">
                                    {{ $menuName }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                @if($menuBadge)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $menuBadge }}
                                    </span>
                                @endif
                                <i class="bi bi-chevron-right text-[11px] transition-transform duration-200 {{ $hasActiveChild ? 'text-indigo-600' : 'text-slate-400' }}"
                                   :class="{'rotate-90 text-indigo-600': openMenus['{{ $menuId }}'] || (menuSearch && menuSearch.trim() !== '')}"></i>
                            </div>
                        </button>

                        <!-- Submenu Items (Auto-expands when searching or when child is active) -->
                        <div x-show="openMenus['{{ $menuId }}'] || (menuSearch && menuSearch.trim() !== '')"
                             x-collapse
                             x-cloak
                             class="pl-8 pr-2 py-1 space-y-0.5 border-l-2 border-slate-200 ml-5 my-1">
                            @foreach($children as $child)
                                @php
                                    $childName = is_object($child) ? $child->name : $child['name'];
                                    $childIcon = is_object($child) ? $child->icon : ($child['icon'] ?? 'bi-circle');
                                    $childBadge = is_object($child) ? $child->badge : ($child['badge'] ?? null);
                                    $childRoute = is_object($child) ? $child->route : ($child['route'] ?? null);

                                    $childEn = is_object($child) ? $child->getTranslatedName('en') : ($child['translations']['en']['name'] ?? $childName);
                                    $childKh = is_object($child) ? $child->getTranslatedName('kh') : ($child['translations']['kh']['name'] ?? $childName);
                                    $childZh = is_object($child) ? $child->getTranslatedName('zh') : ($child['translations']['zh']['name'] ?? $childName);

                                    $childUrl = '#';
                                    if ($childRoute) {
                                        if (str_starts_with($childRoute, '#')) {
                                            $childUrl = $childRoute;
                                        } elseif (\Illuminate\Support\Facades\Route::has($childRoute)) {
                                            $childUrl = route($childRoute);
                                        } else {
                                            $childUrl = url($childRoute);
                                        }
                                    }

                                    // Active state detection
                                    $isChildActive = false;
                                    if ($childRoute) {
                                        if (\Illuminate\Support\Facades\Route::has($childRoute) && request()->routeIs($childRoute, $childRoute . '.*')) {
                                            $isChildActive = true;
                                        } elseif (($childRoute === 'backend.size.index' || $childRoute === 'backend.sizes.index') && request()->routeIs('backend.size.*', 'backend.sizes.*')) {
                                            $isChildActive = true;
                                        } elseif ($childUrl !== '#' && !str_starts_with($childUrl, '#')) {
                                            $cPath = parse_url($childUrl, PHP_URL_PATH);
                                            if (request()->url() === $childUrl || ($cPath && request()->is(trim($cPath, '/')))) {
                                                $isChildActive = true;
                                            }
                                        }
                                    }

                                    $childKeywordsJson = json_encode(array_values(array_unique(array_filter([
                                        strtolower($childName),
                                        strtolower($childEn),
                                        strtolower($childKh),
                                        strtolower($childZh),
                                    ]))), JSON_UNESCAPED_UNICODE);
                                @endphp

                                <a href="{{ $childUrl }}"
                                   x-data="{
                                       childKeywords: {{ $childKeywordsJson }},
                                       parentKeywords: [
                                           '{{ addslashes(strtolower($menuName)) }}',
                                           '{{ addslashes(strtolower($transEn)) }}',
                                           '{{ addslashes(strtolower($transKh)) }}',
                                           '{{ addslashes(strtolower($transZh)) }}'
                                       ],
                                       matchesChild() {
                                           if (!menuSearch || !menuSearch.trim()) return true;
                                           let q = menuSearch.toLowerCase().trim();
                                           return this.childKeywords.some(k => k && k.includes(q)) || this.parentKeywords.some(k => k && k.includes(q));
                                       }
                                   }"
                                   x-show="matchesChild()"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs transition-all group {{ $isChildActive ? 'bg-indigo-50 text-indigo-700 font-semibold border-l-2 border-indigo-600 -ml-0.5' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/60' }}">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <i class="bi {{ $childIcon }} text-xs {{ $isChildActive ? 'text-indigo-600 font-bold' : 'text-slate-400 group-hover:text-indigo-600' }} transition-colors"></i>
                                        <span class="truncate {{ $isChildActive ? 'font-semibold text-indigo-700' : 'font-normal group-hover:font-medium' }}"
                                              x-text="lang === 'kh' ? '{{ addslashes($childKh) }}' : (lang === 'zh' ? '{{ addslashes($childZh) }}' : '{{ addslashes($childEn) }}')">
                                            {{ $childName }}
                                        </span>
                                    </div>

                                    @if($childBadge)
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full uppercase tracking-wider
                                            @if($childBadge === 'Live') bg-emerald-50 text-emerald-700 border border-emerald-200
                                            @elseif($childBadge === 'Sale' || $childBadge === 'Catalog') bg-cyan-50 text-cyan-700 border border-cyan-200
                                            @elseif($childBadge === 'Pending') bg-amber-50 text-amber-700 border border-amber-200
                                            @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
                                            {{ $childBadge }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Active User & Role Display -->
    @php
        $sidebarUser = Auth::user();
        $sidebarRoleBadge = $sidebarUser ? $sidebarUser->role_badge : [
            'title' => 'Guest User',
            'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-400',
            'icon' => 'bi-person',
        ];
        $sidebarRoleTitle = $sidebarUser ? $sidebarUser->role_title : 'Guest User';
    @endphp
    <div class="px-3 py-2.5 border-t border-slate-200 bg-slate-50/70">
        <div class="flex items-center gap-2.5 p-2 rounded-lg bg-white border border-slate-200 shadow-2xs">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-xs">
                {{ strtoupper(substr($sidebarUser->name ?? 'G', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-slate-800 truncate leading-tight">{{ $sidebarUser->name ?? 'Guest User' }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full {{ $sidebarRoleBadge['dot'] }}"></span>
                    <span class="text-[10px] font-medium text-slate-500 truncate">{{ $sidebarRoleTitle }}</span>
                </div>
            </div>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider shrink-0 {{ $sidebarRoleBadge['bg'] }}">
                {{ $sidebarUser->role_slug ?? 'guest' }}
            </span>
        </div>
    </div>

</aside>
