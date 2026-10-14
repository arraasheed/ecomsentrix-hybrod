{{-- 
    Demo Theme Header Styling
    CLAUDE-DEMO Phase 5
    
    Kill-switch: DEMO_THEME_ENABLED=false in .env
--}}

@if (env('DEMO_THEME_ENABLED', false))
  <style>
    /* Demo header - clean minimal design */
    .site-header {
      @apply bg-white sticky top-0 z-40 border-b border-gray-100;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .site-header .logo-col h1 a img {
      @apply h-12 w-auto object-contain;
    }
    
    /* Search bar styling */
    .site-header .search-input input {
      @apply border-gray-200 focus:border-pink-600 focus:ring-pink-600;
    }
    
    .site-header .btn-primary {
      @apply bg-pink-600 hover:bg-pink-700 text-white transition-colors;
    }
    
    /* Header icons - demo color on hover */
    .site-header a:not(.logo-col a) {
      @apply text-gray-600 hover:text-pink-600 transition-colors duration-200;
    }
    
    /* Category dropdown - demo styling */
    .site-header .categories-wrap .btn-primary {
      @apply bg-pink-600 hover:bg-pink-700;
    }
    
    .site-header .category-dropdown {
      @apply bg-white border border-gray-200 rounded-md shadow-lg;
    }
    
    .site-header .category-dropdown li a:hover {
      @apply text-pink-600 bg-gray-50;
    }
    
    /* Mobile menu button */
    .site-header #mobile-menu-toggle {
      @apply text-gray-700 hover:text-pink-600 transition-colors;
    }
  </style>
@endif
