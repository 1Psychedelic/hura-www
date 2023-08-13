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
import ReservationSteps from "./ReservationSteps";

const ReservationAddons = (props) => {

    if (!props.authentication.isLoggedIn) {
        setTimeout(() => {props.addAlert('notLoggedIn', 'danger', 'Pro dokončení rezervace musíte být přihlášen/a.')}, 50);
        props.history.replace(props.url);

        return (<></>);
    }

    if (!props.links.addons.isEnabled) {
        setTimeout(() => {props.addAlert('previousStep', 'danger', 'Před pokračováním je potřeba vyplnit a potvrdit tento formulář.')}, 50);
        props.history.replace(props.url + '/deti');

        return (<></>);
    }

    let createAddonLink = (addon) => {
        if (!addon.link) {
            return (
                <>
                </>
            );
        }

        return (
            <div className="reservation-form-addon-link">
                <a href={addon.link.url} target="_blank">{addon.link.text}</a>
            </div>
        );
    };

    let createAddon = (addon) => {
        return (
            <div key={addon.id} className="reservation-form-addon">
                <div className="reservation-form-addon-icon">
                    <img src={addon.icon} alt={addon.name}/>
                </div>
                <div className="reservation-form-addon-content">
                    <h4 className="reservation-form-addon-name">{addon.name}</h4>
                    <div className="reservation-form-addon-description" dangerouslySetInnerHTML={{__html: addon.description}}/>
                    {createAddonLink(addon)}
                </div>
                <div className="reservation-form-addon-bottom">
                    <div className="reservation-form-addon-price">{addon.priceText}</div>
                    <div className="reservation-form-addon-amount">
                        <div className="reservation-form-addon-amount-subtract">
                            <a href="#" onClick={(e) => {e.preventDefault();props.onAddonSubtract(addon.id)}} className={'reservation-form-addon-amount-button' + (!props.addons[addon.id] ? ' disabled' : '')}>
                                <span>-</span>
                            </a>
                        </div>
                        <div>
                            <div className="reservation-form-addon-amount-current">
                                <span>{!props.addons[addon.id] ? 0 : Math.min(props.countChildren, props.addons[addon.id])}</span>
                            </div>
                        </div>
                        <div className="reservation-form-addon-amount-add">
                            <a href="#" onClick={(e) => {e.preventDefault();props.onAddonAdd(addon.id)}} className={'reservation-form-addon-amount-button' + (props.addons[addon.id] && props.addons[addon.id] >= props.countChildren ? ' disabled' : '')}>
                                <span>+</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    let createAddons = (addons) => {
        return addons.map(createAddon);
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
                        <h3>Doplňkové služby</h3>
                        <div className="reservation-form-subheading">
                            <span>Krok č. 3</span>
                        </div>
                    </div>
                    <div>
                        <ReservationSteps
                            links={props.links}
                            activeLink="addons"
                        />
                    </div>
                </div>

                <div className="reservation-form-addons">
                    {createAddons(props.event.addons)}
                </div>

                <div className="reservation-form-submits">
                    <div className="reservation-form-submits-left">
                    </div>
                    <div className="reservation-form-submits-right">
                        <input onClick={(e) => {props.scrollManager.current.setScrollTarget(props.reservationFormTop);props.onSubmit(e);}} type="submit" className="form-button form-button-large"  disabled={props.form.submit.disabled} value={props.form.submit.value}/>
                        <Link onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}} replace to={props.links.children.url} className="form-button form-button-large form-button-outline-alt">Jít zpět</Link>
                    </div>
                </div>

            </div>
        </div>
    )
};

export default ReservationAddons
