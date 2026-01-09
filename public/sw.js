const CACHE_NAME = 'dashboard-v1';
const urlsToCache = [
  // Apenas arquivos estáticos essenciais que temos certeza que existem
  './css/style.css',
  './js/funcoes.js',
  './js/recursos/usuarios/login.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        // Usar addAll com tratamento de erro individual para não quebrar tudo
        // se um arquivo falhar
        const promises = urlsToCache.map(url => {
          return cache.add(url).catch(err => {
            console.warn('Falha ao cachear arquivo:', url, err);
          });
        });
        return Promise.all(promises);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});