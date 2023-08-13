import React from 'react'
import api from "../Api/Api";


const HomepageSubscribe = (props) => {

    let onSubmit = (e) => {
        e.preventDefault();

        let email = e.target.email.value;

        if (!email) {
            props.addAlert('subscribe', 'danger', 'E-mail není vyplněný.');
            return;
        }

        let body = {
            'email': email
        };

        api.request('POST', 'api/v1/email-subscribe', body, (response, data) => {
            if (response.status === 201) {
                props.addAlert('subscribe', 'success', 'E-mail byl přihlášen k odběru novinek.');
                return;
            }

            if (data && data.error) {
                props.addAlert('subscribe', 'danger', data.error);
                return;
            }

            props.addAlert('subscribe', 'danger', 'E-mail se nepodařilo přihlásit k odběru novinek.');
        });
    };

    let context = props.context || 'homepage';

    if (context === 'homepage') {
        return (
            <section className="page__section page__section--homepage-subscribe">
                <div className="page__section__content">
                    <div>
                        <span className="page__section__subheading">Získejte informace o všech našich akcích</span>
                        <h2 className="page__section__heading">Přihlašte se k odběru novinek:</h2>
                    </div>
                    <form onSubmit={onSubmit}>
                        <div className="form-input-group form-input-group-text">
                            <div className="form-input-group-inner">
                                <label htmlFor={context + "-subscribe-email"}>Zadejte Váš e-mail</label>
                                <input id={context + "-subscribe-email"} name="email" type="email" required={true}/>
                            </div>
                        </div>
                        <div>
                            <input type="submit" className="form-button" value="Odebírat"/>
                        </div>
                    </form>
                </div>
            </section>
        );
    }

    return (
        <form onSubmit={onSubmit}>
            <div className="form-input-group form-input-group-text">
                <div className="form-input-group-inner">
                    <label htmlFor={context + "-subscribe-email"}>Zadejte Váš e-mail</label>
                    <input id={context + "-subscribe-email"} name="email" type="email" required={true}/>
                </div>
            </div>
            <div>
                <input type="submit" className="form-button" value="Odebírat"/>
            </div>
        </form>
    );
};

export default HomepageSubscribe
