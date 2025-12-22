<?php exit();?>
<button id="recaptcha-container">send sms now</button>
<script type="module">
    // Import the functions you need from the SDKs you need
    import {
        initializeApp
    } from "https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js";
    // TODO: Add SDKs for Firebase products that you want to use
    // https://firebase.google.com/docs/web/setup#available-libraries
    import {
        getAuth,
        RecaptchaVerifier,
        signInWithPhoneNumber
    } from 'https://www.gstatic.com/firebasejs/9.17.1/firebase-auth.js'

    const firebaseConfig = {
        apiKey: "AIzaSyCdmaINB0PkvXR8TURahT542OFjRk2pLzc",
        authDomain: "halalofficebv.firebaseapp.com",
        projectId: "halalofficebv",
        storageBucket: "halalofficebv.appspot.com",
        messagingSenderId: "427208905594",
        appId: "1:427208905594:web:008c80c5d7bbfb96557cc3",
        measurementId: "G-NDKZD5VNSF"
    };

    // var firebaseConfig = {
    //     apiKey: "AIzaSyDgL-XH4X5SvUSiX2MTSaL69PeDUvEcwBU",
    //     authDomain: "halal-quality-control.firebaseapp.com",
    //     projectId: "halal-quality-control",
    //     storageBucket: "halal-quality-control.appspot.com",
    //     messagingSenderId: "617300082118",
    //     appId: "1:617300082118:web:89f768500b90301acc86c7",
    //     measurementId: "G-CX4DT3E860"
    // };

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);

    var phoneNumber = "+31640074308";
    window.recaptchaVerifier = new RecaptchaVerifier('recaptcha-container', {
        'size': 'invisible',
        'callback': function(response) {
            // reCAPTCHA solved, allow signInWithPhoneNumber.
            onSignInSubmit();
        }
    }, auth);

    recaptchaVerifier.render().then(function(widgetId) {
        window.recaptchaWidgetId = widgetId;
    });

    var appVerifier = window.recaptchaVerifier;

    // Note: The RecaptchaVerifier here is just a placeholder, and you can replace it with your own UI if needed.
    // If you don't want to use RecaptchaVerifier, you can omit it or create an empty container.

    function onSignInSubmit() {

        signInWithPhoneNumber(auth, phoneNumber, appVerifier)
            .then((confirmationResult) => {
                // SMS sent. Prompt user to type the code from the message, then sign the
                // user in with confirmationResult.confirm(code).
                //window.confirmationResult = confirmationResult;
                var verificationId = confirmationResult.verificationId;
                console.log(confirmationResult);

                // ...
            }).catch((error) => {
                // Error; SMS not sent
                console.error("Error sending verification code: ", error);
            });
    }
    // signInWithPhoneNumber(auth, phoneNumber, appVerifier)
    //     .then(function(confirmationResult) {
    //         // Save the verification ID to use later
    //         var verificationId = confirmationResult.verificationId;
    //         console.log("Verification ID: ", verificationId);
    //     })
    //     .catch(function(error) {
    //         console.error("Error sending verification code: ", error);

    //     });

    // signInWithPhoneNumber(auth, phoneNumber, appVerifier)
    //     .then(function(confirmationResult) {
    //         // SMS sent. Prompt user to type the code from the message.
    //         var verificationCode = prompt('Enter the verification code: ');

    //         return confirmationResult.confirm(verificationCode);
    //     })
    //     .then(function(result) {
    //         // User is signed in successfully.
    //         console.log(result);
    //     })
    //     .catch(function(error) {
    //         // Handle errors here.
    //         console.error(error);
    //     });
</script>
<?php /*
<script type="module">
    // Import the functions you need from the SDKs you need
    import {
        initializeApp
    } from "https://www.gstatic.com/firebasejs/9.0.2/firebase-app.js";
    // TODO: Add SDKs for Firebase products that you want to use
    // https://firebase.google.com/docs/web/setup#available-libraries
    import {
        getAuth,
        signInWithPhoneNumber
    } from 'https://www.gstatic.com/firebasejs/9.0.2/firebase-auth.js'
    // Your web app's Firebase configuration

    // Your web app's Firebase configuration
    var firebaseConfig = {
        apiKey: "AIzaSyDgL-XH4X5SvUSiX2MTSaL69PeDUvEcwBU",
        authDomain: "halal-quality-control.firebaseapp.com",
        projectId: "halal-quality-control",
        storageBucket: "halal-quality-control.appspot.com",
        messagingSenderId: "617300082118",
        appId: "1:617300082118:web:89f768500b90301acc86c7",
        measurementId: "G-CX4DT3E860"
    };

    firebase.initializeApp(firebaseConfig);
    //Initialize Firebase
    const firebase = initializeApp(firebaseConfig);
    // Initialize the Firebase auth service
    const auth = getAuth(firebase);

    console.log(auth);

    // Get the user's phone number from the client
    var phoneNumber = "+31640074308"; // Replace with the user's phone number

    //var appVerifier = new RecaptchaVerifier('recaptcha-container',auth);

    // // Send verification code to the user's phone number
    signInWithPhoneNumber(auth, phoneNumber)
        .then(function(confirmationResult) {
            // Save the verification ID to use later
            var verificationId = confirmationResult.verificationId;
            console.log("Verification ID: ", verificationId);
        })
        .catch(function(error) {
            console.error("Error sending verification code: ", error);
        });
    // return false;
    // // Get the verification code from the user input
    // var verificationCode = "123456"; // Replace with the user's input

    // // Use the verification code to complete the authentication
    // var credential = firebase.auth.PhoneAuthProvider.credential(verificationId, verificationCode);

    // // Sign in with the credential
    // firebase
    //     .auth()
    //     .signInWithCredential(credential)
    //     .then(function(user) {
    //         // User successfully signed in
    //         console.log("User signed in: ", user);
    //     })
    //     .catch(function(error) {
    //         // Verification failed
    //         console.error("Error verifying code: ", error);
    //     });
</script>
*/ ?>