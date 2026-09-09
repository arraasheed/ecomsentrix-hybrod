// SW registration with kill-switch support
// If PWA_ENABLED=false in .env, this unregisters any existing SW

(function() {
  const pwaEnabled = document.documentElement.dataset.pwaEnabled === 'true';
  const storeId = document.documentElement.dataset.storeId;

  if (!navigator.serviceWorker) return;

  if (!pwaEnabled) {
    // Kill-switch: unregister any existing SW
    navigator.serviceWorker.getRegistrations().then(registrations => {
      registrations.forEach(reg => reg.unregister());
    });
    return;
  }

  // Register new SW
  navigator.serviceWorker
    .register('/sw.js', { scope: '/' })
    .then(registration => {
      console.log('[PWA] Service Worker registered:', registration);

      // Check for updates periodically
      setInterval(() => {
        registration.update();
      }, 60000); // Every minute

      // Listen for new SW waiting
      registration.addEventListener('updatefound', () => {
        const newWorker = registration.installing;
        newWorker.addEventListener('statechange', () => {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            console.log('[PWA] New Service Worker available. Page refresh recommended.');
          }
        });
      });
    })
    .catch(error => {
      console.error('[PWA] Service Worker registration failed:', error);
    });

  // Unregister old broken SW on first load
  navigator.serviceWorker.getRegistrations().then(registrations => {
    registrations.forEach(reg => {
      // If it's from the old broken implementation, unregister it
      if (reg.scope === '/') {
        // Clear old caches from broken implementation
        caches.keys().then(names => {
          names.forEach(name => {
            if (!name.includes('v1')) {
              caches.delete(name);
            }
          });
        });
      }
    });
  });
})();
