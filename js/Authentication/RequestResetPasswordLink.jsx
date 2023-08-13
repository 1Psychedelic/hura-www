import React from 'react'
import api from "../Api/Api";
import {Helmet} from "react-helmet";

const RequestResetPasswordLink = (props) => {

    if (props.authentication.isLoggedIn) {
        return (
            <>
            </>
        );
    }

    let onSubmit = (e) => {
        e.preventDefault();

        let email = e.target.email.value;

        let body = {
            'email': email
        };

        api.request('POST', 'api/v1/authentication/request-reset-password-link', body, (response, data) => {
            if (response.status === 200) {
                props.addAlert('requestPasswordReset', 'success', 'Požadavek na obnovení hesla byl přijat. Pokud existuje účet se zadanou e-mailovou adresou, bude na ni odeslán odkaz pro obnovení hesla.');
                return;
            }

            props.addAlert('requestPasswordReset', 'danger', 'Nepodařilo se odeslat e-mail s odkazem pro obnovení hesla.');
        });
    };

    return (
        <>
            <Helmet>
                <title>Obnovit zapomenuté heslo</title>
                <meta name="robots" content="noindex, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">Obnovit zapomenuté heslo</h2>
                </div>
            </section>
            <section className="page__section page__section--user-profile">
                <div className="page__section__content">

                    <div className="reservation-form-wrapper compact">
                        <div className="reservation-form">
                            <div className="reservation-form-heading">
                                <div>
                                    <h3>Obnovit zapomenuté heslo</h3>
                                    <div className="reservation-form-subheading">
                                        <span>Zadejte svůj e-mail, pod kterým se přihlašujete, a my Vám pošleme odkaz pro nastavení nového hesla.</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <form onSubmit={onSubmit}>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner">
                                            <label htmlFor="reset-password-email">Váš e-mail</label>
                                            <input required="required" id="reset-password-email" name="email" type="email"/>
                                        </div>
                                    </div>
                                    <div>
                                        <input type="submit" className="login-button login-button-password" value="Obnovit zapomenuté heslo"/>
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

export default RequestResetPasswordLink;
