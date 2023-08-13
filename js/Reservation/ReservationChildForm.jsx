import React from 'react'
import InputText from "../Form/InputText";
import InputCheckbox from "../Form/InputCheckbox";
import InputSelect from "../Form/InputSelect";
import ReservationFormSidebarChildren from "./ReservationFormSidebarChildren";
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
import ReservationSteps from "./ReservationSteps";

const ReservationChildForm = React.forwardRef((props, ref) => {

    if (!props.authentication.isLoggedIn) {
        setTimeout(() => {props.addAlert('notLoggedIn', 'danger', 'Pro dokončení rezervace musíte být přihlášen/a.')}, 50);
        props.history.replace(props.url);

        return (<></>);
    }

    if (!props.links.children.isEnabled) {
        setTimeout(() => {props.addAlert('previousStep', 'danger', 'Před pokračováním je potřeba vyplnit a potvrdit tento formulář.')}, 50);
        props.history.replace(props.url);

        return (<></>);
    }

    return (
        <div className="reservation-form-wrapper">
            <ReservationFormSidebarChildren
                children={props.children}
                onChildAddToReservation={props.onChildAddToReservation}
                onChildRemoveFromReservation={props.onChildRemoveFromReservation}
            />
            <div className="reservation-form">
                <div className="reservation-form-heading">
                    <div>
                        <h3>Děti v přihlášce</h3>
                        <div className="reservation-form-subheading">
                            <span>Krok č. 2</span>
                        </div>
                    </div>
                    <div>
                        <ReservationSteps
                            links={props.links}
                            activeLink="children"
                        />
                    </div>
                </div>
                <form onSubmit={props.onSubmit}>
                    <div className="form-half-width-container">
                        <div className="form-half-width">
                            <InputText ref={props.form.focus === 'name' ? ref : null} name="name" data={props.form.name} onChange={props.onChange} onBlur={props.onBlur} label="Jméno a příjmení dítěte"/>
                            <InputSelect ref={props.form.focus === 'gender' ? ref : null} name="gender" data={props.form.gender} onChange={props.onChange} onBlur={props.onBlur} label="Pohlaví"/>
                            <InputSelect ref={props.form.focus === 'adhd' ? ref : null} name="adhd" data={props.form.adhd} onChange={props.onChange} onBlur={props.onBlur} label="Má Vaše dítě ADHD nebo podobnou diagnózu?"/>
                        </div>
                        <div className="form-half-width">
                            <InputText ref={props.form.focus === 'dateBorn' ? ref : null} name="dateBorn" data={props.form.dateBorn} onChange={props.onChange} onBlur={props.onBlur} label="Datum narození" type="date"/>
                            <InputSelect ref={props.form.focus === 'swimmer' ? ref : null} name="swimmer" data={props.form.swimmer} onChange={props.onChange} onBlur={props.onBlur} label="Plavec/neplavec"/>
                            <InputSelect ref={props.form.focus === 'firstTimer' ? ref : null} name="firstTimer" data={props.form.firstTimer} onChange={props.onChange} onBlur={props.onBlur} label="Jede dítě na tábor poprvé?"/>
                        </div>
                    </div>
                    <div>
                        <div className={'form-input-group form-input-group-textarea' + (props.form.health.validationError ? ' form-error' : '')}>
                            <div className="form-input-group-inner">
                                <label htmlFor="health">Zdravotní stav dítěte a další důležité věci, které bychom měli vědět:</label>
                                <textarea ref={props.form.focus === 'health' ? ref : null} name="health" id="health" onChange={props.onChange} onBlur={props.onBlur} defaultValue={props.form.health.value} placeholder="Uveďte prosím veškeré důležité zdravotní informace o dítěti, zdravotní omezení, alergie a léky, které užívá."/>
                            </div>
                            <div className="form-error-message">{props.form.health.validationError}</div>
                        </div>
                    </div>
                    <div className="reservation-form-submits">
                        <div className="reservation-form-submits-left">
                            <input type="hidden" name="childId" value={props.form.childId.value}/>
                            <input type="hidden" name="applicationChildId" value={props.form.applicationChildId.value}/>
                        </div>
                        <div className="reservation-form-submits-right">
                            <input onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}} type="submit" className="form-button form-button-large" disabled={props.form.submit.disabled} value={props.form.submit.value}/>
                            <Link onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}} replace to={props.links.children.url} className="form-button form-button-large form-button-outline-alt">Jít zpět</Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    )
});

export default ReservationChildForm
