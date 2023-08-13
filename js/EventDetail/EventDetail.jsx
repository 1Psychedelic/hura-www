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
import EventDetailContent from "./EventDetailContent";
import EventDetailSidebar from "./EventDetailSidebar";
import Error404 from "../Error/Error404";
import {Helmet} from "react-helmet";
import EventDetailDiscountText from "./EventDetailDiscountText";

const EventDetail = (props) => {
    //let { slug } = useParams();
    let path = useLocation().pathname;
    let events = props.events || [];
    let loadedEvents = props.loadedEvents || {};
    let event = null;

    for (var i in events) {
        if ((path + '/').startsWith(events[i].url + '/')) {
            event = events[i];
            break;
        }
    }

    if (event === null) {
        for (var loadedPath in loadedEvents) {
            if ((path + '/').startsWith(loadedPath + '/')) {
                event = loadedEvents[loadedPath];
                break;
            }
        }
    }

    let onDiscountExpired = () => {
        location.reload();
    };

    if (event === null) {
        if (props.initialLoadFinished) {
            props.onEventNotLoaded(path);
        }

        return (
            <>
                <section className="page__section page__section--generic-heading ph-item">
                    <div className="page__section__content">
                        <span className="page__section__subheading">&nbsp;</span>
                        <h2 className="page__section__heading">&nbsp;</h2>
                    </div>
                </section>
                <section className="page__section page__section--event-detail">
                    <div className="page__section__content">

                        <div className="event-detail-wrapper">
                            <div className="event-detail">
                                <div className="ph-item event-detail-banner">
                                    <div className="ph-picture"/>
                                </div>
                                <div className="ph-item event-detail-info-box">
                                    <div className="ph-row">
                                        <div className="ph-col-2"><div className="ph-picture"/></div>
                                        <div className="ph-col-2"><div className="ph-picture"/></div>
                                        <div className="ph-col-2"><div className="ph-picture"/></div>
                                        <div className="ph-col-2"><div className="ph-picture"/></div>
                                    </div>
                                </div>

                                <div className="ph-item event-detail-content">
                                    <div className="ph-picture"/>
                                </div>
                            </div>

                            <div className="event-detail-sidebar ph-item">
                                <div className="ph-picture"/>

                                <div className="event-detail-sidebar-bottom">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                </section>

            </>
        );
    }

    if (event === 404) {
        return (
            <Error404/>
        );
    }

    if (parseInt(event) === event) {
        return (
            <>{event}</>
        );
    }

    let createPrice = (event) => {
        if (event.price === event.priceBeforeDiscount) {
            return (
                <div className="event-detail-info-value">{event.priceText}</div>
            );
        }

        return (
            <div className="event-detail-info-value">
                <div className="event-detail-info-price-discounted">
                    {event.priceText}
                </div>
                <div className="event-detail-info-price-before-discount">
                    {event.priceBeforeDiscountText}
                </div>
            </div>
        );
    };

    let createInfoBox = (event) => {
        if (event.isArchived) {
            return (
                <></>
            );
        }

        let createReservationButton = (event) => {
            if (event.hasOpenApplications) {
                return (
                    <div className="event-detail-info event-detail-reserve-button">
                        <Link to={event.url + '/rezervace'} onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}}>Rezervovat</Link>
                    </div>
                );
            }

            return (
                <div className="event-detail-info event-detail-reserve-button">
                    <span className="disabled">
                        Přihlášky<br />uzavřeny
                    </span>
                </div>
            );
        };

        return (
            <div className="event-detail-info-box">
                <div className="event-detail-info-box-row">
                    <div className="event-detail-info">
                        <div className="event-detail-info-label">Termín:</div>
                        <div className="event-detail-info-value">{event.date}</div>
                    </div>
                    <div className="event-detail-info">
                        <div className="event-detail-info-label">Cena:</div>
                        {createPrice(event)}
                    </div>
                    <div className="event-detail-info">
                        <div className="event-detail-info-label">Věk dětí:</div>
                        <div className="event-detail-info-value">{event.age}</div>
                    </div>
                    <div className="event-detail-info">
                        <div className="event-detail-info-label">Volná místa:</div>
                        <div className="event-detail-info-value">{event.capacity}</div>
                    </div>
                    {createReservationButton(event)}
                </div>
                <EventDetailDiscountText
                    discountExpiresAt={event.discountExpiresAt}
                    onDiscountExpired={onDiscountExpired}
                />
            </div>
        );
    };

    let renderSubheading = () => {
        if (!event.subheading) {
            return (
                <>
                </>
            );
        }

        return (
            <span className="page__section__subheading">{event.subheading}</span>
        );
    };

    return (
        <>
            <Helmet>
                <title>{event.name}</title>
                <meta name="description" content={event.description} />
                <meta property="og:title" content={event.name} />
                <meta property="og:description" content={event.description} />
                <meta property="og:image" content={event.image} />
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    {renderSubheading()}
                    <h1 className="page__section__heading">{event.name}</h1>
                </div>
            </section>
            <section className="page__section page__section--event-detail">
                <div className="page__section__content">

                    <div className="event-detail-wrapper">
                        <div className="event-detail">
                            <img className="event-detail-banner" src={event.bannerLarge} alt={event.name}/>
                            {createInfoBox(event)}

                            <EventDetailContent
                                event={event}
                            />
                        </div>

                        <EventDetailSidebar
                            event={event}
                            events={events}
                            api={props.api}
                            addAlert={props.addAlert}
                        />
                    </div>
                </div>
            </section>
        </>
    );
};

export default EventDetail
