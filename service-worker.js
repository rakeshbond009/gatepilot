const CACHE_NAME = 'gatepilot-v2';

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll([
            './',
            './index.php'
        ]))
    );
});

self.addEventListener('fetch', event => {
    // Skip non-GET requests (POST, etc.) to avoid intercepting form submissions
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});
