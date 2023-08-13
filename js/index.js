import React from "react";
import ReactDOM from "react-dom";
import { Exceptionless } from "@exceptionless/browser";

import App from "./App";

if ("serviceWorker" in navigator) {
    navigator.serviceWorker
        .register("/firebase-messaging-sw.js")
        .then(function(registration) {
            console.log("Registration successful, scope is:", registration.scope);
        })
        .catch(function(err) {
            console.log("Service worker registration failed, error:", err);
        });
}

Exceptionless.startup(c => {
    c.serverUrl = 'https://exceptionless.lukasklika.cz';
    c.apiKey = "irGi7zOziQg9cKmkvf5uVinHG14jTd9ZtaYjlI8p";
}).finally(() => {
    ReactDOM.hydrate(<App initialState={window.__INITIAL_STATE__} />, document.getElementById("app"));
});
