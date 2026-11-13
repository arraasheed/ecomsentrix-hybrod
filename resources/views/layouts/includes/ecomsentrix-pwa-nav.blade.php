<nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-4 py-2 flex items-center justify-around shadow-lg">
    <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('dashboard') ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span>ড্যাশবোর্ড</span>
    </a>
    <a href="{{ route('order.index') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('order.*') ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <span>অর্ডার</span>
    </a>
    <a href="{{ route('product.index') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('product.*') ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <span>প্রোডাক্ট</span>
    </a>
    @if(Route::has('fraud.index'))
    <a href="{{ route('fraud.index') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('fraud.*') ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        <span>ফ্রড চেকার</span>
    </a>
    @endif
    <button type="button" id="mobile-drawer-toggle" class="flex flex-col items-center gap-1 text-xs text-slate-500 hover:text-slate-900">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
        <span>মেনু</span>
    </button>
</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('ecomsentrix-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle-btn');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileDrawerToggle = document.getElementById('mobile-drawer-toggle');

    if (toggleBtn && sidebar) {
        let isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        if (isCollapsed) {
            sidebar.setAttribute('data-collapsed', 'true');
            document.body.classList.add('sidebar-collapsed');
        }

        toggleBtn.addEventListener('click', () => {
            isCollapsed = !isCollapsed;
            sidebar.setAttribute('data-collapsed', isCollapsed);
            localStorage.setItem('sidebar_collapsed', isCollapsed);
            document.body.classList.toggle('sidebar-collapsed', isCollapsed);
        });
    }

    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mob-sidebar-active');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('hidden');
        });
    }

    if (mobileDrawerToggle && sidebar) {
        mobileDrawerToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mob-sidebar-active');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('hidden');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('mob-sidebar-active');
            sidebarOverlay.classList.add('hidden');
        });
    }
});
</script>
<style>
body.sidebar-collapsed #ecomsentrix-sidebar {
    width: 72px !important;
}
body.sidebar-collapsed #ecomsentrix-sidebar .sidebar-text {
    display: none !important;
}
body.sidebar-collapsed .main-content-wrapper {
    padding-left: 72px !important;
}
@media (min-width: 768px) {
    .main-content-wrapper {
        padding-left: 256px;
        transition: padding-left 300ms ease-in-out;
    }
    body.sidebar-collapsed .main-content-wrapper {
        padding-left: 72px;
    }
}
</style>
