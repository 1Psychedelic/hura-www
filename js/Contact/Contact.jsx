import React from 'react'
import {Helmet} from "react-helmet";
import api from "../Api/Api";
import InputText from "../Form/InputText";

const Contact = (props) => {

    let onSubmit = (e) => {
        e.preventDefault();

        let firstName = e.target.firstName.value;
        let lastName = e.target.lastName.value;
        let email = e.target.email.value;
        let subject = e.target.subject.value;
        let message = e.target.message.value;

        if (!firstName || !lastName || !email || !subject || !message) {
            props.addAlert('contact', 'danger', 'Prosím vyplňte všechna políčka');
            return;
        }

        let body = {
            'firstName': firstName,
            'lastName': lastName,
            'email': email,
            'subject': subject,
            'message': message
        };

        api.request('POST', 'api/v1/contact-form/submit', body, (response, data) => {
            if (response.status === 201) {
                props.addAlert('contact', 'success', 'Vaše zpráva byla odeslána.');
                return;
            }

            if (data && data.error) {
                props.addAlert('contact', 'danger', data.error);
                return;
            }

            props.addAlert('contact', 'danger', 'Zprávu se nepodařilo odeslat.');
        });
    };

    return (
        <>
            <Helmet>
                <title>Kontakty</title>
                <meta name="robots" content="index, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">Kontakty</h2>
                </div>
            </section>
            <section className="page__section page__section--contacts">
                <div className="page__section__content">

                    <div className="reservation-form-wrapper">
                        <div className="reservation-form-sidebar contact-page-sidebar">

                            <h3>{props.website.name}</h3>
                            <div>
                                <h4>Adresa:</h4>
                                <span>{props.website.address}</span>

                                <h4>Kontaktní osoba:</h4>
                                <span>
                                    {props.website.contactPerson}<br/>
                                    <a href={'tel:' + props.website.phone}>{props.website.phoneHumanReadable}</a><br/>
                                    <a href={'mailto:' + props.website.email}>{props.website.email}</a>
                                </span>
                                <h4>Ostatní:</h4>
                                <span>
                                    Číslo účtu: {props.website.bankAccount}<br/>
                                    IČ: {props.website.ico}
                                </span>
                            </div>
                        </div>
                        <div className="reservation-form">
                            <div className="reservation-form-heading">
                                <div>
                                    <h3>Napište nám:</h3>
                                    <div className="reservation-form-subheading">
                                        <span></span>
                                    </div>
                                </div>
                                <div>
                                </div>
                            </div>
                            <form onSubmit={onSubmit}>
                                <div className="form-half-width-container">
                                    <div className="form-half-width">
                                        <InputText name="firstName" required={true} label="Jméno" data={{'value': ''}}/>
                                    </div>
                                    <div className="form-half-width">
                                        <InputText name="lastName" required={true} label="Příjmení" data={{'value': ''}}/>
                                    </div>
                                </div>
                                <div>
                                    <InputText name="email" required={true} label="Váš e-mail" data={{'value': ''}} type="email"/>
                                </div>
                                <div>
                                    <InputText name="subject" required={true} label="Předmět" data={{'value': ''}}/>
                                </div>
                                <div className={'form-input-group form-input-group-textarea'}>
                                    <div className="form-input-group-inner">
                                        <label htmlFor="message">Vaše zpráva:</label>
                                        <textarea name="message" id="message" required={true}/>
                                    </div>
                                    <div className="form-error-message"/>
                                </div>
                                <div>
                                    <input type="submit" className="form-button form-button-large" value="Odeslat zprávu"/>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </section>
        </>
    );
}

export default Contact;
