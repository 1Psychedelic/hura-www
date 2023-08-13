var Hafo = Hafo || {};
Hafo.User = {};
Hafo.User.Current = {
    loggedIn: false,
    id: null,
    roles: [],
    data: {}
};

Hafo.User.setCurrentUser = function(current) {
    if (typeof $ === 'undefined') {
        console.warn('JQuery library is not loaded.');
    } else if (!$.nette || !$.nette.ajax) {
        console.warn('Nette.ajax.js library is not loaded.');
    }
    Hafo.User.Current = current;
};
