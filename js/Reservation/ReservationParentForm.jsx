import React from 'react'
import InputText from "../Form/InputText";
import InputCheckbox from "../Form/InputCheckbox";
import ReservationSidebarLogin from "./ReservationSidebarLogin";
import ReservationSteps from "./ReservationSteps";


const ReservationParentForm = React.forwardRef((props, ref) => {

    return (
        <div className="reservation-form-wrapper">
            <ReservationSidebarLogin
                api={props.api}
                authentication={props.authentication}
                addAlert={props.addAlert}
                googleAppId={props.website.google.appId}
                facebookAppId={props.website.facebook.appId}
            />
            <div className="reservation-form">
                <div className="reservation-form-heading">
                    <div>
                        <h3>Zákonný zástupce</h3>
                        <div className="reservation-form-subheading">
                            <span>Krok č. 1</span>
                        </div>
                    </div>
                    <div>
                        <ReservationSteps
                            links={props.links}
                            activeLink="parent"
                        />
                    </div>
                </div>
                <form onSubmit={props.onSubmit}>
                    <div className="form-half-width-container">
                        <div className="form-half-width">
                            <InputText ref={props.form.focus === 'name' ? ref : null} name="name" data={props.form.name} onBlur={props.onBlur} onChange={props.onChange} onRevalidate={props.onRevalidate} required={true} label="Jméno a příjmení zákonného zástupce"/>
                            <InputText ref={props.form.focus === 'phone' ? ref : null} name="phone" data={props.form.phone} onBlur={props.onBlur} onChange={props.onChange} onRevalidate={props.onRevalidate} required={true} type="tel" label="Telefonní číslo"/>
                            <InputText ref={props.form.focus === 'email' ? ref : null} name="email" data={props.form.email} onBlur={props.onBlur} onChange={props.onChange} onRevalidate={props.onRevalidate} required={true} type="email" label="E-mail"/>
                        </div>
                        <div className="form-half-width">
                            <InputText ref={props.form.focus === 'street' ? ref : null} name="street" data={props.form.street} onBlur={props.onBlur} onChange={props.onChange} onRevalidate={props.onRevalidate} required={true} label="Ulice a číslo domu"/>
                            <InputText ref={props.form.focus === 'city' ? ref : null} name="city" data={props.form.city} onBlur={props.onBlur} onChange={props.onChange} onRevalidate={props.onRevalidate} required={true} label="Město"/>
                            <InputText ref={props.form.focus === 'zip' ? ref : null} name="zip" data={props.form.zip} onBlur={props.onBlur} onChange={props.onChange} onRevalidate={props.onRevalidate} required={true} pattern="[0-9]{5}" label="PSČ"/>
                        </div>
                    </div>
                    <div className="reservation-form-checkboxes">
                        <InputCheckbox ref={props.form.focus === 'agreeGdpr' ? ref : null} name="agreeGdpr" data={props.form.agreeGdpr} onChange={props.onChange} label={'Přečetl/a jsem si <a href="' + props.website.gdpr + '" target="_blank">Zásady ochrany osobních údajů</a> a souhlasím s nimi.'}/>
                        <InputCheckbox ref={props.form.focus === 'agreeTermsAndConditions' ? ref : null} name="agreeTermsAndConditions" data={props.form.agreeTermsAndConditions} onChange={props.onChange} label={'Přečetl/a jsem si <a href="' + props.website.termsAndConditions + '" target="_blank">VOP</a> a s nimi související dokument <a href="' + props.website.rules + '" target="_blank">Jak to u nás chodí</a> a souhlasím s nimi.'}/>
                        <InputCheckbox ref={props.form.focus === 'agreeNewsletter' ? ref : null} name="agreeNewsletter" data={props.form.agreeNewsletter} onChange={props.onChange} label='Přeji si dostávat novinky na e-mail.'/>
                    </div>
                    <div className="reservation-form-submits">
                        <div className="reservation-form-submits-left">
                            <input type="hidden" name="wasSubmitted" defaultValue={props.form.wasSubmitted.value || false}/>
                        </div>
                        <div className="reservation-form-submits-right">
                            <input onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}} type="submit" className="form-button form-button-large" disabled={props.form.submit.disabled} value={props.form.submit.value}/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    )
});

export default ReservationParentForm
