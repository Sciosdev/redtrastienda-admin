importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyCO7wsBv5ExQMIOdN7to-jtchV3ZQQl3_4",
    authDomain: "anpec-b7c3c.firebaseapp.com",
    projectId: "anpec-b7c3c",
    storageBucket: "anpec-b7c3c.firebasestorage.app",
    messagingSenderId: "1011581065251",
    appId: "1:1011581065251:web:38bb0add1cca37c692b996",
    measurementId: "G-BHGB17MF5R"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});