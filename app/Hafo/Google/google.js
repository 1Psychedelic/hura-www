var Hafo = Hafo || {};
Hafo.Google = {};

Hafo.Google.button = null;

Hafo.Google.clientId = null;
Hafo.Google.authorizeUrl = null;
Hafo.Google.signingOut = false;

Hafo.Google.onLoad = function() {
    gapi.load('auth2', function() {
        gapi.auth2.init({
            'client_id': Hafo.Google.clientId
        }).then(function() {
            var GoogleAuth = gapi.auth2.getAuthInstance();
            GoogleAuth.isSignedIn.listen(Hafo.Google.statusChange);
            if(!Hafo.User.Current.loggedIn && GoogleAuth.isSignedIn.get()) {
                Hafo.Google.signingOut = true;
                GoogleAuth.signOut();
            }
        });

    });
};

Hafo.Google.onLogin = function() {
    if(Hafo.Google.authorizeUrl === null || Hafo.Google.signingOut) {
        return;
    }
    var GoogleAuth = gapi.auth2.getAuthInstance();
    var user = GoogleAuth.currentUser.get();
    var token = user.getAuthResponse().id_token;
    var complete = function(payload) {
        console.log(payload);
        if(payload.userAgent && typeof Hafo.User !== 'undefined') {
            Hafo.User.setCurrentUser(payload.user);
        }
    };
    $.nette.ajax({
        url: Hafo.Google.authorizeUrl,
        method: 'POST',
        data: {
            token: token
        },
        complete: complete
    });
};

Hafo.Google.onLogout = function() {
    Hafo.Google.signingOut = false;
};

Hafo.Google.statusChange = function(status) {
    if(Hafo.Google.button !== null) {
        $(Hafo.Google.button).addClass('disabled');
        $(Hafo.Google.button).html('Počkejte prosím...');
    }
    if(!status) {
        Hafo.Google.onLogout();
    } else {
        Hafo.Google.onLogin();
    }
};

Hafo.Google.login = function(authorize) {
    Hafo.Google.authorizeUrl = authorize;
    if(typeof gapi === 'undefined') {
        console.warn('Google SDK not loaded.');
        alert('Přihlášení přes Google není v tuto chvíli k dispozici.');
        return;
    }
    var GoogleAuth = gapi.auth2.getAuthInstance();
    GoogleAuth.signIn({
        'scope': 'profile email'
    });
};

window.googleLoginLoaded = function() {
    Hafo.Google.onLoad();
};
