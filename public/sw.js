self.addEventListener('push', function (event) {

    const data = event.data.json();

    const options = {
        body: data.body,
        icon: "/img/logo.png",
        badge: "/img/logo.png",
        data: {
            url: data.url
        }
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});


self.addEventListener('notificationclick', function (event) {

    event.notification.close();

    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );

});