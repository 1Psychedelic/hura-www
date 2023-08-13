var Hafo = Hafo || {};
Hafo.Facebook = {};
Hafo.Facebook.button = null;

Hafo.Facebook.statusChange = function(response, authorize, deauthorize) {
    var complete = function(payload) {
        if(payload.userAgent && typeof Hafo.User !== 'undefined') {
            Hafo.User.setCurrentUser(payload.user);
        }
    };
    if(response.authResponse && response.authResponse.signedRequest && response.status === 'connected') {
        console.log('fb connected');
        $.nette.ajax({
            url: authorize,
            method: 'POST',
            data: {
                status: response.status,
                signedRequest: response.authResponse.signedRequest
            },
            complete: complete
        });
    } else {
        $.nette.ajax({
            url: deauthorize,
            method: 'POST',
            complete: complete
        });
    }
    if(Hafo.Facebook.button !== null) {
        $(Hafo.Facebook.button).addClass('disabled');
        $(Hafo.Facebook.button).html('Počkejte prosím...');
    }
};

Hafo.Facebook.init = function(authorize, deauthorize) {
    if(typeof FB === 'undefined') {
        console.warn('Facebook JS SDK is not loaded.');
    }
    if(typeof $ === 'undefined') {
        console.warn('JQuery library is not loaded.');
    }else if(!$.nette || !$.nette.ajax) {
        console.warn('Nette.ajax.js library is not loaded.');
    }
    if(typeof Hafo.User === 'undefined') {
        console.warn('User JS SDK is not loaded.');
    }
    FB.Event.subscribe('auth.authResponseChange', function(response) {
        Hafo.Facebook.statusChange(response, authorize, deauthorize);
    });
};
