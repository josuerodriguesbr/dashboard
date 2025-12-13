// sw.js
const CACHE_NAME = 'dashboard-v1';
const urlsToCache = [
  '/projetos/dashboard',
  '/projetos/dashboard/public/css/style.css',
  '/projetos/dashboard/public/js/tema.js'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', (event) => {
  // Opcional: adicione lógica de cache aqui se quiser offline
});