import React from 'react'
import ReservationFormSidebarPrice from "./ReservationFormSidebarPrice";
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Redirect,
    Link,
    useParams,
    useLocation,
    useRouteMatch, withRouter
} from "react-router-dom";
import InputCheckbox from "../Form/InputCheckbox";
import ReservationSteps from "./ReservationSteps";
import ReservationAddons from "./ReservationAddons";

const ReservationFinish = (props) => {

    if (!props.authentication.isLoggedIn) {
        setTimeout(() => {props.addAlert('notLoggedIn', 'danger', 'Pro dokončení rezervace musíte být přihlášen/a.')}, 50);
        props.history.replace(props.url);

        return (<></>);
    }

    if (!props.links.finish.isEnabled) {
        setTimeout(() => {props.addAlert('previousStep', 'danger', 'Před pokračováním je potřeba vyplnit a potvrdit tento formulář.')}, 50);
        props.history.replace(props.url + '/doplnkove-sluzby');

        return (<></>);
    }

    let createChild = (child) => {
        return (
            <div key={child.childId} className="reservation-form-finish-child reservation-form-finish-group">
                <h4><strong>{child.name}</strong> (dítě)</h4>
                <div>
                    <div className="reservation-form-finish-child-details">
                        <div>
                            {child.dateBorn}<br/>
                            {child.swimmer ? 'Plavec' : 'Neplavec'}
                        </div>
                        <div>
                            {child.firstTimer ? 'Poprvé na táboře' : 'Už byl na táboře'}<br/>
                            {child.adhd ? 'Má ADHD' : 'Nemá ADHD'}
                        </div>
                    </div>
                    <div className="reservation-form-finish-child-health">
                        {child.health}
                    </div>
                </div>
            </div>
        );
    };

    let createChildren = (children) => {
        return children.map(createChild);
    };

    return (
        <div className="reservation-form-wrapper">
            <ReservationFormSidebarPrice
                event={props.event}
                reservation={props.reservation}
                onPayByCredit={props.onPayByCredit}
                onSetDiscountCode={props.onSetDiscountCode}
            />
            <div className="reservation-form">
                <div className="reservation-form-heading">
                    <div>
                        <h3>Dokončení</h3>
                        <div className="reservation-form-subheading">
                            <span>Krok č. 4</span>
                        </div>
                    </div>
                    <div>
                        <ReservationSteps
                            links={props.links}
                            activeLink="finish"
                        />
                    </div>
                </div>

                <div className="reservation-form-finish">
                    <div className="reservation-form-finish-parent reservation-form-finish-group">
                        <h4><strong>{props.reservation.parentForm.name.value}</strong> (zákonný zástupce)</h4>
                        <div className="reservation-form-finish-parent-contact">
                            <div>
                                {props.reservation.parentForm.street.value}<br/>
                                {props.reservation.parentForm.city.value} {props.reservation.parentForm.zip.value}
                            </div>
                            <div>
                                <div className="reservation-form-finish-parent-email">
                                    <strong>Telefon:</strong> {props.reservation.parentForm.phone.value}
                                </div>
                                <div className="reservation-form-finish-parent-email">
                                    <strong>E-mail:</strong> {props.reservation.parentForm.email.value}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="reservation-form-finish-children">
                        {createChildren(props.reservation.children.filter((child) => child.isInReservation))}
                    </div>
                </div>

                <form onSubmit={props.onSubmit}>

                    <div className={'form-input-group form-input-group-textarea' + (props.form.notes.validationError ? ' form-error' : '')}>
                        <div className="form-input-group-inner">
                            <label htmlFor="notes">Poznámka:</label>
                            <textarea name="notes" id="notes" onChange={props.onChange} onBlur={props.onBlur} defaultValue={props.form.notes.value} placeholder="Pokud si přejete, aby vaše děti byly na pokoji s jiným konkrétním dítětem, uveďte zde celé jméno a příjmení (kluci a holky spí vždy odděleně, i v případě sourozenců)."/>
                        </div>
                        <div className="form-error-message">{props.form.notes.validationError}</div>
                    </div>

                    <div className="reservation-form-checkboxes">
                        <InputCheckbox name="isPayingOnInvoice" data={props.form.isPayingOnInvoice} onChange={props.onChange} label='Tábor mi proplatí zaměstnavatel, potřebuji pro něj vystavit fakturu.'/>
                    </div>

                    <div className="reservation-form-submits">
                        <div className="reservation-form-submits-left">
                        </div>
                        <div className="reservation-form-submits-right">
                            <input onClick={(e) => {props.scrollManager.current.setScrollTarget(props.reservationFormTop);props.onSubmit(e);}} type="submit" className="form-button form-button-large"  disabled={props.form.submit.disabled} value={props.form.submit.value}/>
                            <Link onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}} replace to={props.links.addons.url} className="form-button form-button-large form-button-outline-alt">Jít zpět</Link>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    )
};

export default ReservationFinish
