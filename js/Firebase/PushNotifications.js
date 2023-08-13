import { initializeApp } from 'firebase/app';
import { getMessaging, getToken } from "firebase/messaging";
import api from "../Api/Api";

const firebaseConfig = {
    apiKey: "AIzaSyBe5prrwli5tku6KEOhDGSASmbJTSNfuss",
    authDomain: "hura-tabory-test.firebaseapp.com",
    projectId: "hura-tabory-test",
    storageBucket: "hura-tabory-test.appspot.com",
    messagingSenderId: "693279354368",
    appId: "1:693279354368:web:ef431547abcf1668f9f2e5"
};
const app = initializeApp(firebaseConfig);

Notification.requestPermission().then(function (permission) {
    console.log(permission);
    // If the user accepts, let's create a notification
    if (permission === "granted") {
        const messaging = getMessaging(app);
        getToken(messaging, { vapidKey: 'BJDPCfq_BWA2Oyu5GoARI0wb5JRviL6qMH4kmMSCgsmnUfVX_cHJg6SxTS0efLp17OrAt3YzJFeFgEl3S9bNckE' }).then((currentToken) => {
            if (currentToken) {
                api.request('pOST', 'api/v1/push-notification/register-token', {'token': currentToken}, (response, data) => {
                    console.log(response);
                }).catch(function (response) {
                    console.log(response);
                });
            } else {
                // Show permission request UI
                console.log('No registration token available. Request permission to generate one.');
                // ...
            }
        }).catch((err) => {
            console.log('An error occurred while retrieving token. ', err);
            // ...
        });
    }
});
