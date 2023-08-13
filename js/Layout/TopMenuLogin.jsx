import React from 'react'
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link
} from "react-router-dom";
import api from '../Api/Api'
import GoogleLogin from "../Authentication/GoogleLogin";
import FacebookLogin from "../Authentication/FacebookLogin";

class TopMenuLogin extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            'submit': {
                'value': 'Přihlásit se',
                'disabled': false
            }
        };

        this.onSubmit = this.onSubmit.bind(this);
    }

    onSubmit(event) {
        event.preventDefault();

        this.setState({
            'submit': {
                'value': 'Počkejte prosím...',
                'disabled': true
            }
        });

        let form = event.target;

        let data = {
            'login': form.email.value,
            'password': form.password.value
        };

        this.props.api.request('POST', 'api/v1/authentication/login', data, (response, data) => {
            if (response.status === 200) {
                this.props.addAlert('login', 'success', 'Úspěšně jsme vás přihlásili.');
                this.setState({
                    'submit': {
                        'value': 'Přihlásit se',
                        'disabled': false,
                    },
                });
                return;
            }

            let errorMessage = null;
            if (response.status === 401) {
                errorMessage = 'Nesprávné přihlašovací údaje.';
            } else if (response.status !== 200) {
                errorMessage = 'Došlo k neznámé chybě.';
            }

            this.props.addAlert('login', 'danger', errorMessage);

            this.setState({
                'submit': {
                    'value': 'Přihlásit se',
                    'disabled': false,
                }
            });
        }, false);
    }

    render() {

        let closeDropdown = () => {
            document.getElementById('top-menu-login-button').checked = false;
        };

        var onLogout = (event) => {
            event.preventDefault();
            closeDropdown();

            api.request('POST', 'api/v1/authentication/logout', {}, (response, data) => {
                if (response.status === 200) {
                    this.props.addAlert('login', 'success', 'Úspěšně jsme vás ohlásili.');
                }
            });
        };

        if (this.props.authentication.isLoggedIn === undefined) {
            return (
                <li className="top-menu-login">
                    <a className="button-login">Načítám...</a>
                </li>
            );
        }

        let renderNotifications = () => {
            if (this.props.notifications.countNew > 0) {
                return (
                    <a href="/admin/notifications" id="top-menu-login-notifications">{this.props.notifications.countNew}</a>
                );
            }

            return (
                <>
                </>
            );
        };

        if (this.props.authentication.isLoggedIn) {
            return (
                <li className="top-menu-login">
                    <div>
                        {renderNotifications()}
                    </div>
                    <div>
                        <input id="top-menu-login-button" type="checkbox"/>
                        <label htmlFor="top-menu-login-button" className="top-menu-login-button">Můj účet</label>
                        <label htmlFor="top-menu-login-button" className="top-menu-login-overlay"/>
                        <div className="top-menu-login-dropdown">
                            <label htmlFor="top-menu-login-button" className="top-menu-login-button">Můj účet</label>
                            <div className="top-menu-login-username">
                                <p><strong>{this.props.authentication.userProfile.name}</strong></p>
                            </div>
                            <div className="top-menu-login-avatar">
                                <img src={this.props.authentication.userProfile.avatar} onError={(e) => {e.target.src = '/images/avatar.jpg';}} alt={this.props.authentication.userProfile.name}/>
                            </div>
                            <Link to="/muj-ucet" className="login-button login-button-primary" onClick={closeDropdown}>Zobrazit můj profil</Link>
                            <Link to="/muj-ucet/nastaveni" className="login-button login-button-register" onClick={closeDropdown}>Nastavení a<br/>zabezpečení účtu</Link>
                            <a className="login-button login-button-logout" onClick={onLogout}>Odhlásit se</a>
                        </div>
                    </div>
                </li>
            )
        }

        return (
            <li className="top-menu-login">
                <div/>
                <div>
                    <input id="top-menu-login-button" type="checkbox"/>
                    <label htmlFor="top-menu-login-button" className="top-menu-login-button">Přihlásit se</label>
                    <label htmlFor="top-menu-login-button" className="top-menu-login-overlay"/>
                    <div className="top-menu-login-dropdown">
                        <label htmlFor="top-menu-login-button" className="top-menu-login-button">Přihlásit se</label>
                        <FacebookLogin
                            appId={this.props.facebookAppId}
                            addAlert={this.props.addAlert}
                            api={this.props.api}
                            authentication={this.props.authentication}
                        />
                        <GoogleLogin
                            appId={this.props.googleAppId}
                            addAlert={this.props.addAlert}
                            api={this.props.api}
                            authentication={this.props.authentication}
                        />
                        <div className="top-menu-login-or">
                            <span>nebo</span>
                        </div>
                        <div>
                            <form onSubmit={this.onSubmit}>
                                <div className="form-input-group form-input-group-text">
                                    <div className="form-input-group-inner">
                                        <label htmlFor="top-menu-login-email">Váš e-mail</label>
                                        <input required="required" id="top-menu-login-email" name="email" type="email"/>
                                    </div>
                                </div>
                                <div className="form-input-group form-input-group-text">
                                    <div className="form-input-group-inner">
                                        <label htmlFor="top-menu-login-password">Heslo</label>
                                        <input required="required" id="top-menu-login-password" name="password"
                                               type="password"/>
                                    </div>
                                </div>
                                <div className="top-menu-login-forgotten-password">
                                    <Link to="/obnovit-zapomenute-heslo" onClick={closeDropdown}>Zapomněl/a jsem heslo</Link>
                                </div>
                                <div>
                                    <input type="submit" className="login-button login-button-password" disabled={this.state.submit.disabled} value={this.state.submit.value}/>
                                </div>
                            </form>
                        </div>
                        <div>
                            <Link to="/zaregistrovat-se" className="login-button login-button-register" onClick={closeDropdown}>Zaregistrovat se</Link>
                        </div>
                    </div>
                </div>
            </li>
        )
    }
};

export default TopMenuLogin
