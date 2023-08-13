import React from 'react'
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
import ReservationLoading from "../Reservation/ReservationLoading";
import Error404 from "../Error/Error404";
import UserProfileReservationSidebarPrice from "./UserProfileReservationSidebarPrice";
import {Helmet} from "react-helmet";
import UserProfileReservationInvoiceForm from "./UserProfileReservationInvoiceForm";

const UserProfileReservation = (props) => {

    if (!props.initialLoadFinished) {
        return (
            <ReservationLoading
                reservationFormTop={props.reservationFormTop}
            />
        );
    }

    let {id} = useParams();
    let reservation = undefined;

    if (!props.reservations[id.toString()]) {
        props.onReservationNotLoaded(id);
        return (
            <ReservationLoading
                reservationFormTop={props.reservationFormTop}
            />
        );
    } else {
        reservation = props.reservations[id.toString()];
    }

    if (reservation === 404 || reservation === undefined) {
        return (
            <Error404/>
        );
    }

    if (parseInt(reservation) === reservation) {
        return (
            <>{reservation}</>
        );
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

    let heading = 'Hurá, přihláška úspěšně odeslána!';
    let box = undefined;
    switch (reservation.reservation.status) {
        case 'STATUS_DRAFT':
            heading = 'Nedokončená';
            break;
        case 'STATUS_NEW':
            heading = 'Hurá, přihláška úspěšně odeslána!';
            box = 'Přihláška byla odeslána a čeká na schválení. Na e-mail jsme vám zaslali dodatečné informace.';
            break;
        case 'STATUS_ACCEPTED':
            heading = 'Hurá, přihláška byla schválená!';
            box = 'Přihláška byla schválená, na e-mail jsme vám zaslali dodatečné informace.';
            break;
        case 'STATUS_CANCELED':
        case 'STATUS_REJECTED':
            heading = 'Tato přihláška byla stornována.';
    }

    let renderBox = () => {
        if (box === undefined) {
            return (
                <>
                </>
            );
        }

        return (
            <div className="reservation-flash-success">
                {box}
            </div>
        )
    };

    let renderPaymentOnInvoice = () => {
        if (!reservation.reservation.isPayingOnInvoice) {
            return (
                <>
                </>
            );
        }

        if (!reservation.reservation.invoiceData.isFilled) {
            return (
                <p>Prosíme o vyplnění <strong>údajů o zaměstnavateli</strong> tlačítkem níže, abychom mohli vystavit fakturu.</p>
            );
        }
        return (
            <>
            </>
        );
    };

    let renderPaymentBox = () => {
        if (!reservation.reservation.canBePaidFor) {
            return (
                <>
                </>
            );
        }

        return (
            <div className="reservation-form-finish-group">
                <p>Zálohu ve výši <strong>{reservation.reservation.deposit} Kč</strong> odešlete prosím
                    nejpozději <strong>do 5 pracovních dnů</strong> od schválení přihlášky!</p>
                <p>Zbylou částku doplaťte nejpozději do <strong>{reservation.reservation.paymentDueDate}</strong>, nezaplacené přihlášky mohou být stornovány.</p>
                <p>Platbu odešlete na bankovní účet <strong>{props.website.bankAccount}</strong> s variabilním symbolem <strong>{reservation.reservation.id}</strong>.</p>
                {renderPaymentOnInvoice()}
            </div>
        );
    };

    let renderNotes = () => {
        if (reservation.reservation.notes) {
            return (
                <div className="reservation-form-finish-group">
                    {reservation.reservation.notes}
                </div>
            );
        }

        return (
            <>
            </>
        );
    };

    return (
        <>
            <Helmet>
                <title>Rezervace: {reservation.event.name}</title>
                <meta name="robots" content="noindex, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">Rezervace: {reservation.event.name}</h2>
                </div>
            </section>
            <section className="page__section page__section--reservation-detail">
                <div ref={props.reservationFormTop} className="page__section__content">

                    <div className="reservation-form-wrapper">
                        <UserProfileReservationSidebarPrice
                            event={reservation.event}
                            children={reservation.children}
                            addons={reservation.addons}
                            reservation={reservation.reservation}
                        />
                        <div className="reservation-form">
                            <h3>{heading}</h3>

                            {renderBox()}
                            <div className="reservation-form-finish">

                                {renderPaymentBox()}

                                <UserProfileReservationInvoiceForm
                                    reservation={reservation}
                                    api={props.api}
                                    addAlert={props.addAlert}
                                    initialLoadFinished={props.initialLoadFinished}
                                    onReservationNotLoaded={props.onReservationNotLoaded}
                                />

                                <div className="reservation-form-finish-parent reservation-form-finish-group">
                                    <h4><strong>{reservation.parent.name}</strong> (zákonný zástupce)</h4>
                                    <div className="reservation-form-finish-parent-contact">
                                        <div>
                                            {reservation.parent.street}<br/>
                                            {reservation.parent.city} {reservation.parent.zip}
                                        </div>
                                        <div>
                                            <div className="reservation-form-finish-parent-email">
                                                <strong>Telefon:</strong> {reservation.parent.phone}
                                            </div>
                                            <div className="reservation-form-finish-parent-email">
                                                <strong>E-mail:</strong> {reservation.parent.email}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="reservation-form-finish-children">
                                    {createChildren(reservation.children)}
                                </div>

                                {renderNotes()}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </>
    )
};

export default UserProfileReservation
