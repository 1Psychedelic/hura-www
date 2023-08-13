import React from 'react'
import api from "../Api/Api";
import {Helmet} from "react-helmet";

const ResetPassword = (props) => {

    if (props.authentication.isLoggedIn) {
        return (
            <>
            </>
        );
    }

    let hash = (new URLSearchParams(window.location.search)).get('hash');
    if (!hash) {
        return (
            <>
            </>
        );
    }

    let onSubmit = (e) => {
        e.preventDefault();

        let password1 = e.target.password1.value;
        let password2 = e.target.password2.value;

        if (password1.length < 8) {
            props.addAlert('changePassword', 'danger', 'Prosím zvolte si heslo dlouhé aspoň 8 znaků.');
            return;
        }

        if (password1 !== password2) {
            props.addAlert('changePassword', 'danger', 'Zadaná hesla se neshodují.');
            return;
        }

        let body = {
            'hash': hash,
            'password': password1
        };

        api.request('POST', 'api/v1/authentication/reset-password', body, (response, data) => {
            if (response.status === 200) {
                props.addAlert('changePassword', 'success', 'Nové heslo bylo nastaveno, nyní se s ním můžete přihlásit.');
                return;
            }

            if (data && data.error) {
                props.addAlert('changePassword', 'danger', data.error);
                return;
            }

            props.addAlert('changePassword', 'danger', 'Heslo se nepodařilo změnit.');
        });
    };

    return (
        <>
            <Helmet>
                <title>Změnit heslo</title>
                <meta name="robots" content="noindex, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">Obnovit heslo</h2>
                </div>
            </section>
            <section className="page__section page__section--user-profile">
                <div className="page__section__content">

                    <div className="reservation-form-wrapper compact">
                        <div className="reservation-form">
                            <div className="reservation-form-heading">
                                <div>
                                    <h3>Obnovit heslo</h3>
                                    <div className="reservation-form-subheading">
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <form onSubmit={onSubmit}>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner">
                                            <label htmlFor="new-password1">Zvolte si nové heslo</label>
                                            <input required="required" id="new-password1" name="password1" type="password"/>
                                        </div>
                                    </div>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner">
                                            <label htmlFor="new-password2">Zopakujte heslo</label>
                                            <input required="required" id="new-password2" name="password2" type="password"/>
                                        </div>
                                    </div>
                                    <div>
                                        <input type="submit" className="login-button login-button-password" value="Změnit heslo"/>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </>
    );
}

export default ResetPassword;
