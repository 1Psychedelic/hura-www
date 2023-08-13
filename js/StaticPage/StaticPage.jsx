import React from 'react'
import ReservationFormSidebarPrice from "../Reservation/ReservationFormSidebarPrice";
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
import ReservationLoading from "../Reservation/ReservationLoading";
import Error404 from "../Error/Error404";
import {Helmet} from "react-helmet";

const StaticPage = (props) => {

    if (!props.initialLoadFinished) {
        return (
            <ReservationLoading
                reservationFormTop={props.reservationFormTop}
            />
        );
    }

    let path = useLocation().pathname;
    let loadedStaticPages = props.loadedStaticPages || {};
    let {slug} = useParams();
    let staticPage = null;

    if (!loadedStaticPages[path]) {
        props.onStaticPageNotLoaded(path, slug);
        return (
            <ReservationLoading
                reservationFormTop={props.reservationFormTop}
            />
        );
    }

    staticPage = loadedStaticPages[path] || undefined;

    if (staticPage === 404 || staticPage === undefined) {
        return (
            <Error404/>
        );
    }

    if (parseInt(staticPage) === staticPage) {
        return (
            <>{staticPage}</>
        );
    }

    return (
        <>
            <Helmet>
                <title>{staticPage.name}</title>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h1 className="page__section__heading">{staticPage.name}</h1>
                </div>
            </section>
            <section className="page__section page__section--reservation-detail">
                <div ref={props.reservationFormTop} className="page__section__content">

                    <div className="reservation-form-wrapper">
                        <div className="reservation-form">
                            <h3>{staticPage.name}</h3>
                            <div dangerouslySetInnerHTML={{__html: staticPage.content}}/>
                        </div>
                    </div>
                </div>
            </section>
        </>
    )
};

export default StaticPage
