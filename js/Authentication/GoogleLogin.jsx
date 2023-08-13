import React from 'react'
import {Helmet} from "react-helmet";


class GoogleLogin extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            'apiLoaded': !!window.gapi,
            'isWorking': false
        };

        this.onClick = this.onClick.bind(this);
        this.signIn = this.signIn.bind(this);
        this.loadClientWhenGapiReady = this.loadClientWhenGapiReady.bind(this);
    }

    signIn() {
        gapi.load('auth2', () => {
            let auth2 = gapi.auth2.init({
                client_id: this.props.appId,
                scope: 'profile',
                ux_mode: 'popup'
            });

            auth2.signIn()
                .then((user) => {
                    let data = {
                        'token': user.getAuthResponse().id_token
                    };

                    this.props.api.request('POST', 'api/v1/authentication/google-login', data, (response, data) => {
                        if (response.status === 200) {
                            this.props.addAlert('login', 'success', 'Úspěšně jsme vás přihlásili.');
                            this.setState({'isWorking': false});
                            return;
                        }

                        let errorMessage = null;
                        if (response.status === 401) {
                            errorMessage = 'Ověření přes Google selhalo.';
                        } else if (response.status !== 200) {
                            errorMessage = 'Došlo k neznámé chybě.';
                        }

                        this.props.addAlert('login', 'danger', errorMessage);

                        this.setState({'isWorking': false});
                    }, false);
                })
                .catch((e) => {
                    this.props.addAlert('login', 'danger', 'Přihlášení přes Google se nezdařilo.');
                    this.setState({'isWorking': false});
                });
        });
    }

    loadClientWhenGapiReady(script) {
        if (script.getAttribute('gapi_processed')) {
            this.setState({
                'apiLoaded': true
            }, () => {
                this.signIn();
            });
        } else {
            setTimeout(() => {this.loadClientWhenGapiReady(script)}, 100);
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
                const script = document.createElement("script");
                script.onload = () => {
                    this.loadClientWhenGapiReady(script);
                };
                script.src = "https://apis.google.com/js/platform.js";

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
                    <div className="login-button login-button-google disabled"><img src="/images/icons/icon-google.png" alt="Google logo"/> <span>Počkejte prosím...</span></div>
                </div>
            )
        }

        return (
            <div>
                <a className="login-button login-button-google" onClick={this.onClick}><img src="/images/icons/icon-google.png" alt="Google logo"/> <span>Přihlásit přes <strong>Google</strong></span></a>
            </div>
        );
    }


};

export default GoogleLogin;
