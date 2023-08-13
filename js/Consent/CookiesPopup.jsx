import React from 'react'
import {Link} from "react-router-dom";
import InputCheckbox from "../Form/InputCheckbox";
import Cookies from "js-cookie"


class CookiesPopup extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            'consents': {
                'essential': true,
                'socialLogin': true,
                'analytics': true
            },
            'show': 'bar',
        }

        this.openCustomization = this.openCustomization.bind(this);
        this.onChange = this.onChange.bind(this);
        this.onSubmit = this.onSubmit.bind(this);
    }

    componentDidMount() {
        let cookiesConsent = Cookies.get('cookiesConsent');
        if (cookiesConsent !== undefined) {
            cookiesConsent = JSON.parse(cookiesConsent);

            this.setState({
                'consents': {
                    essential: true,
                    socialLogin: !!cookiesConsent.socialLogin,
                    analytics: !!cookiesConsent.socialLogin,
                },
                'show': 'none'
            });
        }
    }

    openCustomization(e) {
        e.preventDefault();

        this.setState({
            'show': 'popup'
        });
    }

    onChange(name) {
        let consentsState = JSON.parse(JSON.stringify(this.state.consents));
        consentsState[name] = !consentsState[name];

        this.setState({
            'consents': consentsState
        });
    }

    onSubmit() {
        let cookieExpiration = new Date();
        cookieExpiration.setTime(cookieExpiration.getTime() + (1000 * 60 * 60 * 24 * 365 * 5)); // 5 years
        document.cookie = "cookiesConsent=" + JSON.stringify(this.state.consents) + "; expires=" + cookieExpiration.toGMTString();

        this.setState({
            'show': 'none'
        })
    }

    render() {
        if (this.state.show === 'bar') {
            return (
                <div className="cookies-bar-wrapper">
                    <div className="cookies-bar">
                        <div>
                            Tento web používá soubory cookies k poskytování služeb a analýze návštěvnosti.

                            &nbsp;<Link to="#" className="cookies-bar-link">Více informací</Link>
                        </div>
                        <div className="cookies-bar-accept-all">
                            <a onClick={this.onSubmit} className="login-button login-button-password">Souhlasím</a>
                        </div>
                        <div className="cookies-bar-customize">
                            <a href="#" onClick={this.openCustomization} className="cookies-bar-link">Přizpůsobit</a>
                        </div>
                    </div>
                </div>
            );
        }

        if (this.state.show === 'popup') {
            return (
                <div className="cookies-bar-wrapper">
                    <div className="cookies-bar">
                        <div>
                            <InputCheckbox
                                name="essential"
                                data={{validationError: '', value: this.state.consents.essential}}
                                label='Základní cookies nezbytné pro funkčnost webu'
                                disabled={true}
                            />
                            <InputCheckbox
                                name="socialLogin"
                                data={{validationError: '', value: this.state.consents.socialLogin}}
                                label='Cookies pro poskytování přihlášení přes Facebook a Google'
                                onChange={() => {this.onChange('socialLogin')}}
                            />
                            <InputCheckbox
                                name="analytics"
                                data={{validationError: '', value: this.state.consents.analytics}}
                                label='Cookies pro analýzu návštěvnosti'
                                onChange={() => {this.onChange('analytics')}}
                            />
                        </div>
                        <div className="cookies-bar-accept-all">
                            <a onClick={this.onSubmit} className="login-button login-button-password">Přijmout</a>
                        </div>
                    </div>
                </div>
            );
        }

        return (
            <>
            </>
        );
    }
}
export default CookiesPopup;
