import React from 'react'

import Event from "./Event";
import HomepageEvents from "../Homepage/HomepageEvents";
import {Helmet} from "react-helmet";
import Error404 from "../Error/Error404";


class Events extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            'events': props.events || [],
            'initialLoadFinished': props.initialLoadFinished || false
        };

        this.createEvent = this.createEvent.bind(this);
    }

    static getDerivedStateFromProps(props, state) {
        if (props.events !== state.events || props.initialLoadFinished !== state.initialLoadFinished) {
            return {
                'events': props.events,
                'initialLoadFinished': props.initialLoadFinished
            };
        }

        return null;
    }

    createEvent(event) {
        return <Event
            event={event}
            key={event.id}
            scrollManager={this.props.scrollManager}
            reservationFormTop={this.props.reservationFormTop}
        />;
    }

    createEvents(events) {
        return events.map(this.createEvent);
    }

    /*static getDerivedStateFromProps(props, state) {
        if (props.events !== state.events) {
            return {
                'events': props.events
            };
        }

        return null;
    }*/

    render() {
        if (this.state.initialLoadFinished && (!this.state.events || this.state.events.length === 0)) {
            return (
                <>
                    <Error404
                        title={this.props.heading}
                        heading={this.props.heading}
                        subheading={this.props.subheadingEmpty}
                    />
                </>
            )
        }

        return (
            <>
                <Helmet>
                    <title>{this.props.heading}</title>
                </Helmet>
                <section className="page__section page__section--generic-heading">
                    <div className="page__section__content">
                        {this.props.subheading && <span className="page__section__subheading">{this.props.subheading}</span>}
                        <h1 className="page__section__heading">{this.props.heading}</h1>
                    </div>
                </section>
                <section key="events" className="page__section page__section--homepage-events">
                    <div className="page__section__content">
                        <div className="homepage-events homepage-events-wide">
                            {this.createEvents(this.state.events || [])}
                        </div>
                    </div>
                </section>
            </>
        )
    }
};

export default Events
