importScripts("https://www.gstatic.com/firebasejs/5.9.4/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/5.9.4/firebase-messaging.js");
firebase.initializeApp({
    apiKey: "AIzaSyBe5prrwli5tku6KEOhDGSASmbJTSNfuss",
    authDomain: "hura-tabory-test.firebaseapp.com",
    projectId: "hura-tabory-test",
    storageBucket: "hura-tabory-test.appspot.com",
    messagingSenderId: "693279354368",
    appId: "1:693279354368:web:ef431547abcf1668f9f2e5"
});
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    const promiseChain = clients
        .matchAll({
            type: "window",
            includeUncontrolled: true
        })
        .then(windowClients => {
            for (let i = 0; i < windowClients.length; i++) {
                const windowClient = windowClients[i];
                windowClient.postMessage(payload);
            }
        })
        .then(() => {
            return registration.showNotification("my notification title");
        });
    return promiseChain;
});
self.addEventListener('notificationclick', function(event) {
    // do what you want
    // ...
});
