{{-- 
    Demo Theme Footer Styling
    CLAUDE-DEMO Phase 5
    
    Kill-switch: DEMO_THEME_ENABLED=false in .env
--}}

@if (env('DEMO_THEME_ENABLED', false))
  <style>
    /* Demo footer: 4 cols (lg) → 2 cols (md) → 1 col (sm) */
    .site-footer .grid {
      @apply grid-cols-1 md:grid-cols-2 lg:grid-cols-4;
    }
    
    /* Demo footer styling - matches Levo Leather reference */
    .site-footer {
      @apply bg-gray-900 text-white;
    }
    
    .site-footer h4,
    .site-footer .text-lg {
      @apply text-pink-600 font-semibold;
    }
    
    .site-footer a {
      @apply hover:text-pink-600 transition-colors duration-200;
    }
    
    .site-footer .footer-logo img {
      @apply max-h-12 w-auto;
    }
    
    /* Social icons - demo style */
    .site-footer .flex.gap-4 a {
      @apply w-8 h-8 flex items-center justify-center rounded-full bg-pink-600 text-white hover:bg-pink-700;
    }
    
    /* Newsletter button */
    .site-footer button[type="submit"] {
      @apply bg-pink-600 hover:bg-pink-700 text-white transition-colors;
    }
  </style>
@endif
