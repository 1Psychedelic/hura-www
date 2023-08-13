import React from 'react'
import api from "../Api/Api";
import {Helmet} from "react-helmet";

const Registration = (props) => {

    if (props.authentication.isLoggedIn) {
        return (
            <>
            </>
        );
    }

    let onSubmit = (e) => {
        e.preventDefault();

        let name = e.target.name.value;
        let email = e.target.email.value;
        let password1 = e.target.password1.value;
        let password2 = e.target.password2.value;

        if (password1.length < 8) {
            props.addAlert('register', 'danger', 'Prosím zvolte si heslo dlouhé aspoň 8 znaků.');
            return;
        }

        if (password1 !== password2) {
            props.addAlert('register', 'danger', 'Zadaná hesla se neshodují.');
            return;
        }

        let body = {
            'email': email,
            'name': name,
            'password': password1
        };

        api.request('POST', 'api/v1/authentication/register', body, (response, data) => {
            if (response.status === 201) {
                props.addAlert('register', 'success', 'Účet byl vytvořen, ale zatím není aktivní. Pro potvrzení registrace prosím klikněte na odkaz, který jsme Vám poslali e-mailem.');
                return;
            }

            if (data && data.error) {
                props.addAlert('register', 'danger', data.error);
                return;
            }

            props.addAlert('register', 'danger', 'Účet se nepodařilo vytvořit.');
        });
    };

    return (
        <>
            <Helmet>
                <title>Registrace</title>
                <meta name="robots" content="noindex, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">Registrace</h2>
                </div>
            </section>
            <section className="page__section page__section--user-profile">
                <div className="page__section__content">

                    <div className="reservation-form-wrapper compact">
                        <div className="reservation-form">
                            <div className="reservation-form-heading">
                                <div>
                                    <h3>Registrace</h3>
                                    <div className="reservation-form-subheading">
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <form onSubmit={onSubmit}>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner">
                                            <label htmlFor="registration-name">Vaše jméno a příjmení</label>
                                            <input required="required" id="registration-name" name="name" type="text"/>
                                        </div>
                                    </div>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner">
                                            <label htmlFor="registration-email">Váš e-mail</label>
                                            <input required="required" id="registration-email" name="email" type="email"/>
                                        </div>
                                    </div>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner">
                                            <label htmlFor="registration-password1">Zvolte si heslo</label>
                                            <input required="required" id="registration-password1" name="password1" type="password"/>
                                        </div>
                                    </div>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner">
                                            <label htmlFor="registration-password2">Zopakujte heslo</label>
                                            <input required="required" id="registration-password2" name="password2" type="password"/>
                                        </div>
                                    </div>
                                    <div>
                                        <input type="submit" className="login-button login-button-password" value="Zaregistrovat se"/>
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

export default Registration;
