/**
 * Cabinet360 Service Worker
 * Enables offline functionality and caching
 */

const CACHE_NAME = 'cabinet360-v1.0.0';
const CACHE_ASSETS = [
    './',
    './index.php',
    './assets/css/style.css',
    './assets/js/script.js',
    './assets/js/clients.js',
    './assets/js/cases.js',
    './assets/js/appointments.js',
    './assets/js/payments.js',
    './assets/js/tasks.js',
    './assets/icons/icon-192x192.png',
    './assets/icons/icon-512x512.png',
    './manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://code.jquery.com/jquery-3.7.0.min.js'
];

// Install Service Worker
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing Service Worker...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching app shell...');
                return cache.addAll(CACHE_ASSETS);
            })
            .catch((error) => {
                console.error('[Service Worker] Error during installation:', error);
            })
    );
    self.skipWaiting();
});

// Activate Service Worker
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating Service Worker...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch Strategy: Network First, Fallback to Cache
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin) && 
        !event.request.url.includes('cdn.jsdelivr.net') &&
        !event.request.url.includes('cdnjs.cloudflare.com') &&
        !event.request.url.includes('code.jquery.com')) {
        return;
    }

    // Handle different request types
    if (event.request.method === 'GET') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    // Clone the response before caching
                    const responseToCache = response.clone();
                    
                    // Cache successful responses
                    if (response.status === 200) {
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    
                    return response;
                })
                .catch(() => {
                    // If network fails, try cache
                    return caches.match(event.request)
                        .then((cachedResponse) => {
                            if (cachedResponse) {
                                return cachedResponse;
                            }
                            
                            // If no cache, return offline page for navigation requests
                            if (event.request.mode === 'navigate') {
                                return caches.match('/index.php');
                            }
                            
                            // For other requests, return a basic response
                            return new Response('Offline - Resource not available', {
                                status: 503,
                                statusText: 'Service Unavailable',
                                headers: new Headers({
                                    'Content-Type': 'text/plain'
                                })
                            });
                        });
                })
        );
    }
});

// Handle background sync (for future implementation)
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync triggered:', event.tag);
    if (event.tag === 'sync-data') {
        event.waitUntil(syncData());
    }
});

async function syncData() {
    console.log('[Service Worker] Syncing data in background...');
    // Future implementation for offline data sync
}

// Push notification handler (for future implementation)
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push notification received');
    const options = {
        body: event.data ? event.data.text() : 'Nouvelle notification',
        icon: '/assets/icons/icon-192x192.png',
        badge: '/assets/icons/icon-192x192.png',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        }
    };
    
    event.waitUntil(
        self.registration.showNotification('Cabinet360', options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow('/')
    );
});

