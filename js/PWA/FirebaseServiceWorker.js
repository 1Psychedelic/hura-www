import { initializeApp } from 'firebase/app';
import { getMessaging } from "firebase/messaging";
import { onBackgroundMessage } from "firebase/messaging/sw";

const firebaseConfig = {
    apiKey: "AIzaSyBe5prrwli5tku6KEOhDGSASmbJTSNfuss",
    authDomain: "hura-tabory-test.firebaseapp.com",
    projectId: "hura-tabory-test",
    storageBucket: "hura-tabory-test.appspot.com",
    messagingSenderId: "693279354368",
    appId: "1:693279354368:web:ef431547abcf1668f9f2e5"
};
const app = initializeApp(firebaseConfig);

const messaging = getMessaging(app);
onBackgroundMessage(messaging, (payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    // Customize notification here
    const notificationTitle = 'Background Message Title';
    const notificationOptions = {
        body: 'Background Message body.',
        icon: '/firebase-logo.png'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
