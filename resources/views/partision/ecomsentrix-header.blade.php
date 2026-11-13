{{-- eCom Sentrix Modern Header --}}
<header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 sticky top-0 z-30 transition-all duration-300">
    <div class="flex items-center gap-4">
        <div class="relative hidden sm:block">
            <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" placeholder="Search orders, products..." class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 w-64">
        </div>
    </div>
    
    <div class="flex items-center gap-4">
        <a href="{{ route('store.slug', $store->slug) }}" target="_blank" class="text-sm text-slate-600 hover:text-indigo-600 flex items-center gap-2">
            <i class="ti ti-external-link"></i> <span class="hidden sm:inline">Storefront</span>
        </a>
        <button id="theme-toggle" class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
            <i class="ti ti-sun dark:ti-moon text-xl"></i>
        </button>
        <button class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg relative">
            <i class="ti ti-bell text-xl"></i>
            <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500 border border-white"></span>
        </button>
    </div>
</header>
