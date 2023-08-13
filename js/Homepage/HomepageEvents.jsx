import React from 'react'

import HomepageEvent from './HomepageEvent';

class HomepageEvents extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            'events': props.events || []
        };

        this.createHomepageEvent = this.createHomepageEvent.bind(this);
    }

    createHomepageEvent(event) {
        return (
            <HomepageEvent
                event={event}
                key={event.id}
                scrollManager={this.props.scrollManager}
                reservationFormTop={this.props.reservationFormTop}
                refPageTop={this.props.refPageTop}
            />
        );
    }

    createHomepageEvents(events) {
        return events.map(this.createHomepageEvent);
    }

    static getDerivedStateFromProps(props, state) {
        if (props.events !== state.events) {
            return {
                'events': props.events
            };
        }

        return null;
    }

    render() {
        if (!this.props.initialLoadFinished) {
            return (
                <section key="homepage-events" className="page__section page__section--homepage-events">
                    <div className="page__section__content">
                    <span className="page__section__subheading">Dopřejte svým dětem jedinečné zážitky. <img
                        src="/images/homepage/homepage-subheading-star.png" alt=""/></span>
                        <h1 className="page__section__heading">Letní a víkendové tábory pro děti!</h1>

                        <div className="homepage-events">
                            <div className="homepage-event ph-item">
                                <div className="ph-picture homepage-event-banner"/>
                                <div className="homepage-event-content">
                                    <div className="homepage-event-bottom">
                                    </div>
                                </div>
                            </div>
                            <div className="homepage-event ph-item">
                                <div className="ph-picture homepage-event-banner"/>
                                <div className="homepage-event-content">
                                    <div className="homepage-event-bottom">
                                    </div>
                                </div>
                            </div>
                            <div className="homepage-event ph-item">
                                <div className="ph-picture homepage-event-banner"/>
                                <div className="homepage-event-content">
                                    <div className="homepage-event-bottom">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="homepage-events-arrow"/>
                    </div>
                </section>
            );
        }

        return (
            <section key="homepage-events" className="page__section page__section--homepage-events">
                <div className="page__section__content">
                    <span className="page__section__subheading">{this.props.slogan} <img
                        src="/images/homepage/homepage-subheading-star.png" alt=""/></span>
                    <h1 className="page__section__heading">{this.props.heading}</h1>

                    <div className="homepage-events">
                        {this.createHomepageEvents(this.state.events)}
                    </div>
                    <div className="homepage-events-arrow"/>
                </div>
            </section>
        )
    }
};

export default HomepageEvents
