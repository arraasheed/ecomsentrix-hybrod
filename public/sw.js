const CACHE_VERSION = 'v1';
const RUNTIME_CACHE = `runtime-${CACHE_VERSION}`;
const ASSET_CACHE = `assets-${CACHE_VERSION}`;
const IMAGE_CACHE = `images-${CACHE_VERSION}`;

const SECURE_ROUTES = [
  '/login',
  '/logout',
  '/register',
  '/forgot-password',
  '/reset-password',
  '/checkout',
  '/account',
  '/cart',
  '/wishlist',
  '/order',
  '/api/cart',
  '/api/order',
  '/api/auth',
  '/api/account',
];

const NEVER_CACHE_PATTERNS = [
  /\/login/i,
  /\/logout/i,
  /\/register/i,
  /\/checkout/i,
  /\/payment/i,
  /\/admin/i,
  /\/dashboard/i,
  /\/api\/auth/i,
  /\/api\/cart/i,
  /\/api\/order/i,
];

function isSecureRoute(url) {
  return NEVER_CACHE_PATTERNS.some(pattern => pattern.test(url));
}

function shouldCacheResponse(response) {
  if (!response || response.status !== 200) return false;
  if (response.headers.get('Set-Cookie')) return false;
  return true;
}

function getStrategyForUrl(url, method) {
  const pathname = new URL(url).pathname;

  if (method !== 'GET') return 'NetworkOnly';
  if (isSecureRoute(pathname)) return 'NetworkOnly';
  if (pathname.endsWith('.js') || pathname.endsWith('.css')) return 'CacheFirst';
  if (pathname.match(/\.(jpg|jpeg|png|gif|svg|webp)$/i)) return 'StaleWhileRevalidate';
  if (pathname.startsWith('/api/')) return 'NetworkFirst';
  if (pathname.endsWith('.html') || pathname === '/') return 'NetworkFirst';

  return 'NetworkFirst';
}

async function handleNetworkFirst(request, timeout = 3000) {
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);

    const response = await fetch(request, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (shouldCacheResponse(response)) {
      const cache = await caches.open(RUNTIME_CACHE);
      cache.put(request, response.clone());
    }

    return response;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) return cached;

    if (request.destination === 'document') {
      return caches.match('/offline.html') || new Response('Offline', { status: 503 });
    }

    throw error;
  }
}

async function handleCacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  const response = await fetch(request);
  if (shouldCacheResponse(response)) {
    const cache = await caches.open(ASSET_CACHE);
    cache.put(request, response.clone());
  }

  return response;
}

async function handleStaleWhileRevalidate(request) {
  const cached = await caches.match(request);

  const fetchPromise = fetch(request).then(response => {
    if (shouldCacheResponse(response)) {
      const cache = caches.open(IMAGE_CACHE);
      cache.then(c => c.put(request, response.clone()));
    }
    return response;
  });

  return cached || fetchPromise;
}

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(ASSET_CACHE).then(cache => {
      return cache.addAll([
        '/offline.html',
      ]);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(name => !name.startsWith(CACHE_VERSION))
          .map(name => caches.delete(name))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = request.url;

  if (!url.startsWith('http') || url.includes('chrome-extension')) {
    return;
  }

  const strategy = getStrategyForUrl(url, request.method);

  if (strategy === 'NetworkFirst') {
    event.respondWith(handleNetworkFirst(request));
  } else if (strategy === 'CacheFirst') {
    event.respondWith(handleCacheFirst(request));
  } else if (strategy === 'StaleWhileRevalidate') {
    event.respondWith(handleStaleWhileRevalidate(request));
  } else {
    event.respondWith(fetch(request));
  }
});
