@extends('backend.layouts.app', ['title' => $title ?? 'Module'])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 font-medium">Dashboard</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ $title }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
        </div>

        <div class="flex items-center gap-2">
            <button class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium shadow-2xs transition-colors flex items-center gap-1.5">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>
            <button class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i>
                <span>Add {{ Str::singular($title) }}</span>
            </button>
        </div>
    </div>

    <!-- White Content Card -->
    <div class="rounded-2xl bg-white border border-slate-200 p-8 shadow-xs text-center">
        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4 border border-indigo-100 shadow-2xs">
            <i class="bi bi-box-seam text-2xl"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-900 mb-1">{{ $title }} Module Ready</h2>
        <p class="text-xs text-slate-500 max-w-md mx-auto mb-6">
            This module backend controller and routes (<code class="text-indigo-600 font-mono font-bold">{{ $moduleKey }}</code>) are prepared and wired to the menu system.
        </p>

        <div class="inline-flex items-center gap-3">
            <a href="{{ route('dashboard') }}"
               class="px-4 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-medium transition-colors">
                Back to Dashboard
            </a>
            <a href="{{ route('backend.menu.json') }}"
               target="_blank"
               class="px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 text-xs font-semibold transition-colors">
                View in menu.json
            </a>
        </div>
    </div>
</div>
@endsection
