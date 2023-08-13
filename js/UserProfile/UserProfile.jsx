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
import {Helmet} from "react-helmet";
import UserProfileSidebarDiscounts from "./UserProfileSidebarDiscounts";

const UserProfile = (props) => {

    if (!props.authentication.isLoggedIn) {
        return (
            <>
                <section className="page__section page__section--generic-heading">
                    <div className="page__section__content">
                        <span className="page__section__subheading">&nbsp;</span>
                        <h2 className="page__section__heading">Můj profil</h2>
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

    let renderExpiration = (expiration) => {
        return (
            <div key={expiration.expiration}>
                {expiration.amount} Kč má platnost do {expiration.expiration}.
            </div>
        );
    };

    let renderExpirations = () => {
        return props.authentication.credits.expirations.map(renderExpiration);
    };

    let renderCredits = () => {
        if (props.authentication.credits.total <= 0) {
            return (
                <>
                </>
            )
        }

        return (
            <>
                <h3>Moje slevy</h3>
                <p>Máte kredit <strong>{props.authentication.credits.total} Kč</strong>, který můžete využít jako slevu.</p>
                {renderExpirations()}
                <p>&nbsp;</p>
            </>
        );
    };

    if (!props.authentication.reservationListPages || props.authentication.reservationListPages[props.authentication.reservationListLastPage] === undefined) {
        props.onReservationListNotLoaded(props.authentication.reservationListLastPage);
    }

    let renderReservation = (reservation) => {
        let status = '';
        switch (reservation.reservation.status) {
            case 'STATUS_DRAFT':
                status = 'Nedokončená';
                break;
            case 'STATUS_NEW':
                status = 'Nová';
                break;
            case 'STATUS_ACCEPTED':
                status = 'Schválená';
                break;
            case 'STATUS_CANCELED':
            case 'STATUS_REJECTED':
                status = 'Stornovaná';
        }

        return (
            <Link key={reservation.reservation.id} className="user-profile-reservation" to={'/muj-ucet/rezervace/' + reservation.reservation.id}>
                <div>
                    <h4>{reservation.event.name}</h4>
                    <div><strong>Termín akce: {reservation.event.date}</strong></div>
                </div>
                <div>
                    <h4>{reservation.reservation.id}</h4>
                    <div><strong>Stav: {status}</strong></div>
                </div>
            </Link>

            /*

            <div key={reservation.reservation.id} className="user-profile-reservation">

                <h4>{reservation.event.name}</h4>
                {reservation.reservation.status}

            </div>
             */
        );
    };

    let renderReservationListPage = (page, reservations) => {
        let reservationList = (
            <div className="form-button disabled">
                Žádné další přihlášky
            </div>
        );

        if (Object.values(reservations).length) {
            reservationList = (
                <>
                    {Object.values(reservations).map(renderReservation)}
                </>
            );
        }

        return (
            <div key={page}>
                {reservationList}
            </div>
        );
    };

    let renderReservationList = () => {
        if (!props.authentication.reservationListPages[1]) {
            return (
                <>
                    Načítám...
                </>
            );
        }

        if (Object.values(props.authentication.reservationListPages[1]).length === 0) {
            return (
                <>
                    Žádné přihlášky k zobrazení
                </>
            );
        }

        let reservationList = Object.keys(props.authentication.reservationListPages).map((page) => {
            return renderReservationListPage(page, props.authentication.reservationListPages[page]);
        });

        let loadMoreButton = (
            <></>
        );

        if (Object.values(props.authentication.reservationListPages[props.authentication.reservationListLastPage]).length) {
            loadMoreButton = (
                <a className="form-button" href="#" onClick={(e) => {e.preventDefault();props.onReservationListNotLoaded(props.authentication.reservationListLastPage + 1)}}>
                    Načíst další přihlášky
                </a>
            );
        }

        return (
            <>
                {reservationList}
                <div>
                    {loadMoreButton}
                </div>
            </>
        );
    };

    let renderVip = () => {
        let remainingEvents = props.authentication.userProfile.remainingEventsForVip;

        if (remainingEvents === 0) {
            return (
                <>
                    <p>
                        <strong>Děkujeme,</strong> že s námi jezdíte pravidelně a budujete stálý kolektiv kamarádů!
                    </p>
                    <p>
                        <strong>Jste členem našeho věrnostního klubu</strong> a účast na dalších akcích máte
                        za nejnižší možnou cenu.
                    </p>
                </>
            );
        }

        let remainingText = 'ještě ' + remainingEvents + ' akcí.';
        if (remainingEvents === 1) {
            remainingText = 'už jen jedné akce!';
        }

        return (
            <>
                <p>
                    Rodiče dětí, které se k nám vracejí a pomáhají tak budovat stálý kolektiv, odměňujeme členstvím ve věrnostním
                    klubu <strong>s garancí nejnižší ceny</strong>.
                </p>
                <p>
                    Pro získání členství ve věrnostním klubu je potřeba se zúčastnit <strong>{remainingText}</strong>
                </p>
            </>
        );
    };

    return (
        <>
            <Helmet>
                <title>Můj účet</title>
                <meta name="robots" content="noindex, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">Můj profil</h2>
                </div>
            </section>
            <section className="page__section page__section--user-profile">
                <div className="page__section__content">

                    <div className="reservation-form-wrapper">
                        <div className="reservation-form-sidebar">
                            <UserProfileSidebarDiscounts
                                authentication={props.authentication}
                            />
                        </div>
                        <div className="reservation-form">
                            <div className="reservation-form-heading">
                                <div>
                                    <h3>Moje přihlášky</h3>
                                    <div className="reservation-form-subheading">
                                        <span></span>
                                    </div>
                                </div>
                                <div>
                                </div>
                            </div>
                            <div>
                                {renderReservationList()}
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </>
    );
}

export default UserProfile;
