var api = (function () {

    var accessToken = '';

    var onAuthCallback = (data) => {};

    var onNotificationCallback = (data) => {};

    function getBaseUrl() {
        return '/';
    }

    function getAccessToken() {
        /*if (accessToken) {
            return accessToken;
        }

        accessToken = loadAccessToken();*/

        return accessToken;
    }

    function loadAccessToken() {
        return localStorage.getItem('accessToken');
    }

    function setAccessToken(token) {
        accessToken = token;
    }


    return {
        setOnAuthCallback: function (callback) {
            onAuthCallback = callback;
        },
        setOnNotificationCallback: function (callback) {
            onNotificationCallback = callback;
        },
        setAccessToken: function (token) {
            accessToken = token;
        },
        request: function (method, endpoint, body, responseCallback, onAuth, signal) {
            let headers = {
                'Content-Type': 'application/json'
            };
            body = body || {};
            let accessToken = getAccessToken();
            if (accessToken) {
                headers['Authorization'] = 'Bearer ' + accessToken;
            }

            let options = {
                'method': method,
                //'cache': 'no-cache',
                'headers': headers,
            };

            if (method !== 'GET' && method !== 'HEAD') {
                options.body = JSON.stringify(body);
            }

            if (signal) {
                options.signal = signal;
            }

            let firstTry = true;

            let responseHandler = (response) => {
                return response.text().then((text) => {
                    let data = {};
                    try {
                        data = text.length ? JSON.parse(text) : {}
                    } catch (error) {
                        data = {};
                    }
                    console.log({'api': endpoint, 'headers': headers, 'body': body, 'data': data});
                    if (data.authentication) {
                        if (data.authentication.isLoggedIn && data.authentication.accessToken) {
                            setAccessToken(data.authentication.accessToken);
                        } else {
                            setAccessToken('');
                        }
                        onAuthCallback(data);
                        if (onAuth) {
                            onAuth(data);
                        }
                    }

                    if (data.notifications) {
                        onNotificationCallback(data);
                    }

                    if (responseCallback) {
                        return responseCallback(response, data);
                    }
                }).catch((e) => {
                    console.log(e);
                });
            };

            let responseHandlerWrapped = (response) => {
                if (response.status === 401 && firstTry && onAuth !== false) {
                    console.log('Unauthorized! Trying to refresh token.');
                    return fetch(getBaseUrl() + 'api/v1/authentication/refresh-token', {'method': 'GET'}).then((response) => {
                        if (response.status !== 200) {
                            setAccessToken('');
                            let unauthorizedData = {
                                'authentication': {
                                    'isLoggedIn': false
                                }
                            };
                            onAuthCallback(unauthorizedData)
                            if (onAuth) {
                                onAuth(unauthorizedData);
                            }
                        } else {
                            return response.json().then((data) => {
                                setAccessToken(data.accessToken);

                                firstTry = false;
                                options.headers['Authorization'] = 'Bearer ' + data.accessToken;
                                return fetch(getBaseUrl() + endpoint, options).then(responseHandlerWrapped)
                            });
                        }
                    });
                }

                return responseHandler(response);

                //return response;
            };

            if (!accessToken && endpoint === 'api/v1/authentication/user') {
                return responseHandlerWrapped({
                    'status': 401
                });
            }
            return fetch(getBaseUrl() + endpoint, options).then(responseHandlerWrapped);
        }
    };
})();

export default api;
