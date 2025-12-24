(function () {
    const CACHE_VER = '{{app.cache.ver}}';

    const includes = [
        '/styles.css',
        '/script.js',
        '/manifest.json',
        '/favicon.ico',
        /^\/img\/*/,
        /^\/favicon-\d+\.png$/,
        /^\/appicon-\d+\.png$/
    ];

    self.addEventListener('install', function (e) {
        self.skipWaiting();
        e.waitUntil(
            caches.open(CACHE_VER).then(async function (cache) {
                for (const url of includes) {
                    if (typeof url !== 'string') continue;
                    try {
                        await cache.add(url);
                    } catch (err) { }
                }
            }).catch(function () { })
        );
    });

    self.addEventListener('fetch', function (e) {
        const url = new URL(e.request.url);
        const path = url.pathname;
        const match = includes.some(function (pattern) {
            return typeof pattern === 'string'
                ? path.startsWith(pattern)
                : pattern.test(path);
        });
        if (!match) return;
        e.respondWith(
            caches.open(CACHE_VER).then(function (cache) {
                return cache.match(e.request).then(function (res) {
                    const net = fetch(e.request).then(function (nres) {
                        cache.put(e.request, nres.clone());
                        return nres;
                    }).catch(function () { });
                    return res || net;
                }).catch(function () { });
            })
        );
    });

    self.addEventListener('activate', function (e) {
        self.skipWaiting();
        e.waitUntil(
            caches.keys().then(function (keys) {
                return Promise.all(
                    keys.map(function (key) {
                        if (key !== CACHE_VER) {
                            return caches.delete(key);
                        }
                    })
                );
            }).then(function () {
                self.clients.claim();
            }).catch(function () { })
        );
    });
})();
