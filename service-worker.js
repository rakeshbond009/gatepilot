// GatePilot Service Worker - Network-First (No offline cache)
// Removed caching to prevent chrome-error://chromewebdata/ redirect conflicts
// when browser restores tabs from an error state.

self.addEventListener('install', event => {
    self.skipWaiting(); // Activate immediately
});

self.addEventListener('activate', event => {
    // Clear ALL old caches on activate
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.map(key => caches.delete(key)))
        ).then(() => self.clients.claim())
    );
});

// Network-only: always fetch live, never serve from cache
self.addEventListener('fetch', event => {
    // Only handle same-origin GET requests
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith(self.location.origin)) return;

    event.respondWith(fetch(event.request));
});
