<div class="mobile-bottom-bar d-lg-none">
    <div class="mobile-bar-items">
        <a href="{{ route('dashboard') }}" class="bar-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <i class="ti ti-layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('product.index') }}" class="bar-item {{ Request::routeIs('product.index') ? 'active' : '' }}">
            <i class="ti ti-package"></i>
            <span>Product</span>
        </a>
        <a href="{{ route('order.index') }}" class="bar-item {{ Request::routeIs('order.index') ? 'active' : '' }}">
            <i class="ti ti-shopping-cart"></i>
            <span>Orders</span>
        </a>
        <a href="{{ Route::has('notificationengine.index') ? route('notificationengine.index') : '#' }}" class="bar-item {{ Request::routeIs('notificationengine.index') ? 'active' : '' }}">
            <i class="ti ti-bell"></i>
            <span>Alerts</span>
        </a>
        <a href="#!" class="bar-item" id="mobile-bottom-sidebar-toggle">
            <i class="ti ti-menu-2"></i>
            <span>Menu</span>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bottomToggle = document.getElementById('mobile-bottom-sidebar-toggle');
    if (bottomToggle) {
        bottomToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const realToggle = document.getElementById('mobile-collapse');
            if (realToggle) {
                realToggle.click();
            }
        });
    }
});
</script>
