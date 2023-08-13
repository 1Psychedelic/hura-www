import React from 'react'
import api from '../Api/Api'
import GoogleLogin from "../Authentication/GoogleLogin";
import FacebookLogin from "../Authentication/FacebookLogin";
import {Link} from "react-router-dom";
import UserProfileSidebarDiscounts from "../UserProfile/UserProfileSidebarDiscounts";

class ReservationSidebarLogin extends React.Component {
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
        }, false)
    }

    render() {
        if (this.props.authentication.isLoggedIn) {
            return (
                <div className="reservation-form-sidebar">
                    <h3>Vítejte,</h3>
                    <div className="reservation-form-sidebar-subheading">
                        <span>{this.props.authentication.userProfile.name}</span>
                    </div>

                    <UserProfileSidebarDiscounts
                        authentication={this.props.authentication}
                    />
                </div>
            )
        }

        return (
            <div className="reservation-form-sidebar">
                <h3>Máte už účet?</h3>
                <div className="reservation-form-sidebar-subheading">
                    <span>Přihlašte se, prosím!</span>
                </div>
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
                <div className="reservation-form-sidebar-or">
                    <span>nebo</span>
                </div>
                <div className="reservation-form-sidebar-login">
                    <form onSubmit={this.onSubmit}>
                        <div className="form-input-group form-input-group-text">
                            <div className="form-input-group-inner">
                                <label htmlFor="reservation-sidebar-login-email">Váš e-mail</label>
                                <input required="required" id="reservation-sidebar-login-email" name="email" type="email"/>
                            </div>
                        </div>
                        <div className="form-input-group form-input-group-text">
                            <div className="form-input-group-inner">
                                <label htmlFor="reservation-sidebar-login-password">Heslo</label>
                                <input required="required" id="reservation-sidebar-login-password" name="password" type="password"/>
                            </div>
                        </div>
                        <div>
                            <input type="submit" className="login-button login-button-password" disabled={this.state.submit.disabled} value={this.state.submit.value}/>
                        </div>
                    </form>
                </div>
                <div className="reservation-form-sidebar-bottom">
                    <Link to="/obnovit-zapomenute-heslo" className="link-forgotten-password">Zapomněl/a jsem heslo</Link>
                </div>
                <div>
                    Nebo můžete pokračovat bez přihlášení, účet vám založíme a na e-mail pošleme odkaz pro nastavení hesla.
                </div>
            </div>
        )
    };
}

export default ReservationSidebarLogin
