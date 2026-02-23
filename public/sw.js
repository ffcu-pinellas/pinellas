const CACHE_NAME = 'pinellas-fcu-v1';
const urlsToCache = [
  '/',
  '/assets/global/css/custom.css',
  '/assets/global/js/custom.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});
