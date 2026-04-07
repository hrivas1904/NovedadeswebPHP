async function initPush() {
    if (!("serviceWorker" in navigator)) return;
    if (!("PushManager" in window)) return;

    const registration = await navigator.serviceWorker.register("/sw.js");

    const permission = await Notification.requestPermission();

    if (permission !== "granted") return;

    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
    });

    $.ajax({
        url: "/push/subscribe",
        type: "POST",
        data: JSON.stringify(subscription),
        contentType: "application/json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function () {
            console.log("Subscription guardada correctamente");
        },
        error: function (err) {
            console.error("Error guardando subscription", err);
        },
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, "+")
        .replace(/_/g, "/");

    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}

document.addEventListener("DOMContentLoaded", initPush);
