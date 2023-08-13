import React from 'react'

class FacebookLogin extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            'apiLoaded': !!window.FB,
            'isWorking': false,
            'rerequestEmail': false
        };

        this.onClick = this.onClick.bind(this);
        this.signIn = this.signIn.bind(this);
        this.loadClientWhenApiReady = this.loadClientWhenApiReady.bind(this);
    }

    signIn() {
        let params = {
            scope: 'public_profile,email',
            return_scopes: true
        };

        if (this.state.rerequestEmail) {
            params.auth_type = 'rerequest';
            params.scope = 'email';
        }

        FB.login((response) => {
            if (response.status === 'connected' && response.authResponse && response.authResponse.signedRequest && response.authResponse.grantedScopes) {
                if (!response.authResponse.grantedScopes.includes('email')) {
                    this.props.addAlert('login', 'danger', 'Bez oprávnění číst vaši e-mailovou adresu vás bohužel nemůžeme přihlásit.');
                    this.setState({
                        'isWorking': false,
                        'rerequestEmail': true
                    });
                } else {

                    let data = {
                        'signedRequest': response.authResponse.signedRequest
                    };

                    this.props.api.request('POST', 'api/v1/authentication/facebook-login', data, (response, data) => {
                        if (response.status === 200) {
                            this.props.addAlert('login', 'success', 'Úspěšně jsme vás přihlásili.');
                            this.setState({
                                'isWorking': false,
                                'rerequestEmail': false
                            });
                            return;
                        }

                        let errorMessage = null;
                        if (response.status === 401) {
                            errorMessage = 'Ověření přes Facebook selhalo.';
                        } else if (response.status !== 200) {
                            errorMessage = 'Došlo k neznámé chybě.';
                        }

                        this.props.addAlert('login', 'danger', errorMessage);

                        this.setState({'isWorking': false});
                    }, false);
                }
            } else {
                this.props.addAlert('login', 'danger', 'Přihlášení přes Facebook se nezdařilo.');
                this.setState({'isWorking': false});
            }
        }, params);
    }

    loadClientWhenApiReady(script) {
        if (window.FB) {
            this.setState({
                'apiLoaded': true
            }, () => {
                this.signIn();
            });
        } else {
            setTimeout(() => {this.loadClientWhenApiReady(script)}, 100);
        }
    }

    onClick() {
        if (this.state.isWorking) {
            return;
        }

        this.setState({
            'isWorking': true
        }, () => {
            if (!this.state.apiLoaded) {

                window.fbAsyncInit = () => {
                    FB.init({
                        appId      : this.props.appId,
                        cookie     : true,
                        xfbml      : false,
                        version    : 'v12.0'
                    });
                }

                const script = document.createElement("script");
                script.onload = () => {
                    this.loadClientWhenApiReady(script);
                };
                script.src = "https://connect.facebook.net/cs_CZ/sdk.js";

                document.body.appendChild(script);
            } else {
                this.signIn();
            }
        });
    }

    render() {
        if (!this.props.appId) {
            return (
                <>
                </>
            )
        }

        if (this.state.isWorking) {
            return (
                <div>
                    <div className="login-button login-button-facebook disabled"><img src="/images/icons/icon-facebook.png" alt="Facebook logo"/> <span>Počkejte prosím...</span></div>
                </div>
            )
        }

        return (
            <div>
                <a className="login-button login-button-facebook" onClick={this.onClick}><img src="/images/icons/icon-facebook.png" alt="Facebook logo"/> <span>Přihlásit přes <strong>Facebook</strong></span></a>
            </div>
        );
    }

};

export default FacebookLogin;
