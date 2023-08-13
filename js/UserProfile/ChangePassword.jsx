import React from 'react'
import InputText from "../Form/InputText";
import api from "../Api/Api";
import {Helmet} from "react-helmet";
import FacebookConnect from "../Authentication/FacebookConnect";
import GoogleConnect from "../Authentication/GoogleConnect";

const ChangePassword = (props) => {

    if (!props.authentication.isLoggedIn) {
        return (
            <>
                <section className="page__section page__section--generic-heading">
                    <div className="page__section__content">
                        <span className="page__section__subheading">&nbsp;</span>
                        <h2 className="page__section__heading">Změnit heslo</h2>
                    </div>
                </section>
                <section className="page__section page__section--user-profile">
                    <div className="page__section__content">

                        <h3>Tato stránka vyžaduje přihlášení</h3>

                    </div>
                </section>
            </>
        );
    }

    let onSubmit = (e) => {
        e.preventDefault();

        let oldPassword = e.target.password.value;
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
            'oldPassword': oldPassword,
            'newPassword': password1
        };

        api.request('POST', 'api/v1/authentication/change-password', body, (response, data) => {
            if (response.status === 200) {
                props.addAlert('changePassword', 'success', 'Heslo bylo změněno.');
                return;
            }

            if (data && data.error) {
                props.addAlert('changePassword', 'danger', data.error);
                return;;
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
                    <h2 className="page__section__heading">Změnit heslo</h2>
                </div>
            </section>
            <section className="page__section page__section--user-profile">
                <div className="page__section__content">

                    <div className="reservation-form-wrapper compact">
                        <div className="reservation-form">
                            <div className="reservation-form-heading">
                                <div>
                                    <h3>Změnit heslo</h3>
                                    <div className="reservation-form-subheading">
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <form onSubmit={onSubmit}>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner">
                                            <label htmlFor="old-password">Zadejte své současné heslo</label>
                                            <input required="required" id="old-password" name="password" type="password"/>
                                        </div>
                                    </div>
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

export default ChangePassword;
