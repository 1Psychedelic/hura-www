import React from 'react'
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link,
    useParams,
    useLocation,
    useRouteMatch
} from "react-router-dom";
import ReservationForm from "./ReservationForm";
import Error404 from "../Error/Error404";
import ReservationFormSidebarPrice from "./ReservationFormSidebarPrice";
import InputText from "../Form/InputText";
import InputCheckbox from "../Form/InputCheckbox";
import ReservationLoading from "./ReservationLoading";
import {Helmet} from "react-helmet";
import ErrorBoundary from "../Error/ErrorBoundary";

const Reservation = (props) => {
    if (!props.initialLoadFinished) {
        return (
            <ReservationLoading
                reservationFormTop={props.reservationFormTop}
            />
        );
    }

    let { dummy, url } = useRouteMatch();

    let path = useLocation().pathname + '/';
    let events = props.events || [];
    let event = null;
    for (var i in events) {
        if (path.startsWith(events[i].url + '/')) {
            event = events[i];
            break;
        }
    }

    if (!event) {
        return (
            <>
                <Error404/>
            </>
        );
    }

    //let step = useParams().step || null;

    return (
        <>
            <Helmet>
                <title>Rezervace: {event.name}</title>
                <meta name="robots" content="noindex, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">{event.date}</span>
                    <h2 className="page__section__heading">Rezervace: {event.name}</h2>
                </div>
            </section>
            <section className="page__section page__section--reservation-detail">
                <div ref={props.reservationFormTop} className="page__section__content">

                    <ErrorBoundary>
                        <ReservationForm
                            event={event}
                            url={url}
                            api={props.api}
                            onChildrenChange={props.onChildrenChange}
                            authentication={props.authentication}
                            scrollManager={props.scrollManager}
                            reservationFormTop={props.reservationFormTop}
                            addAlert={props.addAlert}
                            dismissAlert={props.dismissAlert}
                            website={props.website}
                        />
                    </ErrorBoundary>
                </div>
            </section>
        </>
    )
};

export default Reservation
