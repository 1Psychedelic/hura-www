import React from 'react'
import api from "../Api/Api";

const SetPassword = (props) => {

    if (!props.authentication.isLoggedIn || props.authentication.userProfile.loginMethods.password) {
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
            props.addAlert('setPassword', 'danger', 'Prosím zvolte si heslo dlouhé aspoň 8 znaků.');
            return;
        }

        if (password1 !== password2) {
            props.addAlert('setPassword', 'danger', 'Zadaná hesla se neshodují.');
            return;
        }

        let body = {
            'password': password1
        };

        api.request('POST', 'api/v1/authentication/set-password', body, (response, data) => {
            if (response.status === 201) {
                props.addAlert('setPassword', 'success', 'Heslo bylo nastaveno.');
                props.onPasswordSet();
                return;
            }

            props.addAlert('setPassword', 'danger', 'Heslo se nepodařilo nastavit.');
        });
    };

    return (
        <form onSubmit={onSubmit}>
            <div className="form-input-group form-input-group-text">
                <div className="form-input-group-inner">
                    <label htmlFor="set-password1">Zvolte si heslo</label>
                    <input required="required" id="set-password1" name="password1" type="password"/>
                </div>
            </div>
            <div className="form-input-group form-input-group-text">
                <div className="form-input-group-inner">
                    <label htmlFor="set-password2">Zopakujte heslo</label>
                    <input required="required" id="set-password2" name="password2" type="password"/>
                </div>
            </div>
            <div>
                <input type="submit" className="login-button login-button-password" value="Nastavit heslo"/>
            </div>
        </form>
    );
}

export default SetPassword;
