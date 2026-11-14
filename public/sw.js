/*
 * Service worker de Many Champs.
 *
 * Alcance deliberado: la app se instala y los archivos estaticos cargan desde cache,
 * pero los DATOS (paginas, marcadores, sanciones) siempre vienen del servidor.
 * Nunca se cachea HTML ni respuestas Inertia: un marcador viejo es peor que "sin conexion".
 */
const VERSION = 'v1';
const SHELL_CACHE = `mc-shell-${VERSION}`;
const ASSET_CACHE = `mc-assets-${VERSION}`;
const OFFLINE_URL = '/offline.html';

const SHELL = [OFFLINE_URL, '/icons/icon-192.png', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(SHELL_CACHE).then((cache) => cache.addAll(SHELL)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k.startsWith('mc-') && ![SHELL_CACHE, ASSET_CACHE].includes(k))
                    .map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// Archivos con hash de Vite, iconos y fuentes: inmutables, cache primero
const isStaticAsset = (url) =>
    url.pathname.startsWith('/build/assets/') ||
    url.pathname.startsWith('/icons/') ||
    url.hostname === 'fonts.bunny.net';

// Cada deploy cambia el hash (app-1a2b3c4d.js); se borra la version anterior del mismo archivo
const HASHED = /-[0-9a-f]{8}\.(js|css)$/;
const dropOldVersions = async (cache, url) => {
    if (!HASHED.test(url.pathname)) return;
    const stem = url.pathname.replace(HASHED, '');
    const ext = url.pathname.split('.').pop();
    for (const req of await cache.keys()) {
        const path = new URL(req.url).pathname;
        if (path !== url.pathname && HASHED.test(path) && path.replace(HASHED, '') === stem && path.endsWith(ext)) {
            await cache.delete(req);
        }
    }
};

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Navegacion completa: siempre red; sin red, pantalla offline
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.open(ASSET_CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                if (cached) return cached;
                const response = await fetch(request);
                if (response.ok || response.type === 'opaque') {
                    await dropOldVersions(cache, url);
                    cache.put(request, response.clone());
                }
                return response;
            })
        );
    }

    // Todo lo demas (Inertia XHR, API, avatares) pasa directo a la red
});
