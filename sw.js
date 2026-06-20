// sw.js - Service Worker لـ Remedy Pharmacy
// يعمل في الخلفية لاستقبال إشعارات المتصفح

const CACHE_NAME = 'remedy-pharmacy-v1';

self.addEventListener('install', function(event) {
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(clients.claim());
});

// استقبال Push Notification (عند توفر HTTPS)
self.addEventListener('push', function(event) {
    let data = { title: 'Remedy Pharmacy', body: 'إشعار جديد', icon: './Images/icon.png' };
    
    if (event.data) {
        try { data = event.data.json(); } catch(e) { data.body = event.data.text(); }
    }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body:    data.body,
            icon:    data.icon || './Images/WhatsApp Image 2026-02-17 at 10.48.46 PM.jpeg',
            badge:   data.icon || './Images/WhatsApp Image 2026-02-17 at 10.48.46 PM.jpeg',
            vibrate: [200, 100, 200],
            tag:     'remedy-notif',
            data:    { url: data.url || './' }
        })
    );
});

// عند الضغط على الإشعار - فتح الموقع
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const targetUrl = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : './';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (let client of clientList) {
                if (client.url.includes('RemedyPharmacy') && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) return clients.openWindow(targetUrl);
        })
    );
});
