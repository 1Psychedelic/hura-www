import React from 'react'
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
import InputCheckbox from "../Form/InputCheckbox";
import ReservationSteps from "./ReservationSteps";

const ReservationChildren = React.forwardRef((props, ref) => {

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

    let createChild = (child) => {
        return (
            <div key={child.childId} className="reservation-form-child">
                <div className="reservation-form-child-top">
                    <div className="reservation-form-child-buttons">
                        <a href="#" onClick={(event) => {event.preventDefault();props.onChildEdit(child.childId);}} className="reservation-form-child-button reservation-form-child-button-edit">
                            upravit
                        </a>
                        <a href="#" onClick={(event) => {event.preventDefault();props.onChildRemoveFromReservation(child.childId);}} className="reservation-form-child-button reservation-form-child-button-remove">
                            odebrat
                        </a>
                    </div>
                    <div className="reservation-form-child-name">
                        <h4>{child.name}</h4>
                    </div>
                </div>
                <div className="reservation-form-child-details">
                    <div>
                        <div>{child.dateBorn}</div>
                        <div>{(child.swimmer ? 'Plavec' : 'Neplavec')}</div>
                    </div>
                    <div>
                        <div>{(child.firstTimer ? 'Poprvé na táboře' : 'Už byl na táboře')}</div>
                        <div>{(child.adhd ? 'Má ADHD' : 'Nemá ADHD')}</div>
                    </div>
                    <div>
                        {child.health}
                    </div>
                </div>
            </div>
        );
    };

    let createChildren = (children) => {
        return children.filter((child) => {
            return child.isInReservation;
        }).map(createChild);
    };

    let createForm = () => {
        return (
            <form onSubmit={props.onSubmit} key="form">
                <div className="reservation-form-children">
                    {createChildren(props.children)}

                    <div className="reservation-form-checkboxes">
                        <InputCheckbox ref={props.form.focus === 'confirmation' ? ref : null} name="confirmation" data={props.form.confirmation} onChange={props.onChange} label='Závazně prohlašuji, že údaje vyplněné v přihlášce odpovídají skutečnosti.'/>
                    </div>
                </div>

                <div className="reservation-form-submits">
                    <div className="reservation-form-submits-left">
                    </div>
                    <div className="reservation-form-submits-right">
                        <input onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}} type="submit" className="form-button form-button-large" value={props.form.submit.value} disabled={props.form.submit.disabled}/>
                        <a href="#" onClick={props.onChildAdd} className="form-button form-button-large form-button-outline">Přidat dítě</a>
                        <Link onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}} replace to={props.links.parent.url} className="form-button form-button-large form-button-outline-alt">Jít zpět</Link>
                    </div>
                </div>
            </form>
        );
    };

    let createEmpty = () => {
        return (
            <div key="empty">
                <div className="reservation-form-children">
                    <p>Vyberte ze svého seznamu nebo použijte tlačítko níže.</p>
                </div>

                <div className="reservation-form-submits">
                    <div className="reservation-form-submits-left">
                    </div>
                    <div className="reservation-form-submits-right">
                        <div>
                            <a href="#" onClick={props.onChildAdd} className="form-button form-button-large">Přidat dítě</a>
                            <input type="submit" className="form-button form-button-large" value="Uložit a pokračovat" disabled={true}/>
                        </div>
                        <Link onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}} replace to={props.links.parent.url} className="form-button form-button-large form-button-outline-alt">Jít zpět</Link>
                    </div>
                </div>
            </div>
        );
    };

    let createContent = () => {
        if (props.children.filter((child) => {return child.isInReservation;}).length > 0) {
            return createForm();
        }

        return createEmpty();
    };

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

                {createContent()}
            </div>
        </div>
    )
});

export default ReservationChildren
