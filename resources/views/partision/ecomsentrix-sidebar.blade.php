{{-- eCom Sentrix Modern DropX Collapsible Sidebar --}}
@php
    $logo = asset(Storage::url('uploads/logo/'));
    $company_logo = \App\Models\Utility::GetLogo();
    $company_logo = get_file($company_logo);
    $store = getCurrentStore();
@endphp

<aside id="ecomsentrix-sidebar" class="fixed top-0 left-0 z-40 h-screen transition-all duration-300 ease-in-out border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 w-64 data-[collapsed=true]:w-18 flex flex-col justify-between">
    {{-- Header / Brand --}}
    <div>
        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-100 dark:border-slate-800">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 overflow-hidden group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white font-bold text-lg">
                    ES
                </div>
                <span class="text-lg font-bold text-slate-800 dark:text-white group-data-[collapsed=true]:hidden transition-opacity">Sentrix</span>
            </a>
            <button id="sidebar-toggle" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
                <i class="ti ti-menu-2 text-xl"></i>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="p-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800">
                <i class="ti ti-layout-dashboard text-xl"></i>
                <span class="data-[collapsed=true]:hidden">Dashboard</span>
            </a>
            <a href="{{ route('order.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800">
                <i class="ti ti-shopping-cart text-xl"></i>
                <span class="data-[collapsed=true]:hidden">Orders</span>
            </a>
            <a href="{{ route('product.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800">
                <i class="ti ti-shopping-bag text-xl"></i>
                <span class="data-[collapsed=true]:hidden">Products</span>
            </a>
            <div class="pt-4 pb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 data-[collapsed=true]:hidden">Growth & AI</div>
            <a href="{{ route('smart-features.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800">
                <i class="ti ti-sparkles text-xl"></i>
                <span class="data-[collapsed=true]:hidden">Features Hub</span>
            </a>
            <a href="{{ route('fraud.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800">
                <i class="ti ti-shield-alert text-xl"></i>
                <span class="data-[collapsed=true]:hidden">Fraud Shield</span>
            </a>
        </nav>
    </div>

    {{-- Footer --}}
    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
            <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600">
                <i class="ti ti-user"></i>
            </div>
            <div class="data-[collapsed=true]:hidden truncate">
                <p class="text-xs font-semibold text-slate-700 dark:text-white">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-500">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">eCom Sentrix</span>
            </div>
            <span class="text-[10px] font-mono text-slate-400 bg-white dark:bg-slate-700 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-600">v1.0</span>
        </div>
    </div>
</aside>
