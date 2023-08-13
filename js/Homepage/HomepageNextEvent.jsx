import React from 'react'
import {Link} from "react-router-dom";
import smoothScroll from "../Helper/SmoothScroll";


class HomepageNextEvent extends React.Component {

    countdownInterval = null;

    constructor(props) {
        super(props);
        this.state = {
            'nextEvent': props.nextEvent || null,
            'countdown': null
        };
    }

    componentDidMount() {
        this.countdownInterval = setInterval(() => {
            var date = null;
            if (this.state.nextEvent && this.state.nextEvent.starts) {
                date = this.state.nextEvent.starts;
            }
            var nextEventDate = new Date(date);
            var now = new Date().getTime();
            var interval = nextEventDate - now;
            var days = Math.floor(interval / (1000 * 60 * 60 * 24));
            var hours = Math.floor((interval % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((interval % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((interval % (1000 * 60)) / 1000);

            this.setState({
                'countdown': {
                    'days': days,
                    'hours': hours,
                    'minutes': minutes,
                    'seconds': seconds
                }
            });
        }, 1000);
    }

    componentWillUnmount() {
        clearInterval(this.countdownInterval);
    }

    static getDerivedStateFromProps(props, state) {
        if (props.nextEvent !== state.nextEvent) {
            return {
                'nextEvent': props.nextEvent
            };
        }

        return null;
    }

    render() {

        if (!this.props.initialLoadFinished || this.state.countdown === null) {
            return (
                <section key="homepage-next-event" className="page__section page__section--homepage-next-event">
                    <div className="page__section__content">
                        <span className="page__section__subheading"/>
                        <h2 className="page__section__heading">Nejbližší akce začíná už za:</h2>

                        <div className="homepage-next-event">
                            <div>
                                <div className="homepage-next-event-circle homepage-next-event-days">
                                    <div className="homepage-next-event-content">
                                        <div className="homepage-next-event-number">?</div>
                                        <div className="homepage-next-event-label">Dní</div>
                                    </div>
                                </div>
                                <div className="homepage-next-event-circle homepage-next-event-hours">
                                    <div className="homepage-next-event-content">
                                        <div className="homepage-next-event-number">?</div>
                                        <div className="homepage-next-event-label">Hodin</div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div className="homepage-next-event-circle homepage-next-event-minutes">
                                    <div className="homepage-next-event-content">
                                        <div className="homepage-next-event-number">?</div>
                                        <div className="homepage-next-event-label">Minut</div>
                                    </div>
                                </div>
                                <div className="homepage-next-event-circle homepage-next-event-seconds">
                                    <div className="homepage-next-event-content">
                                        <div className="homepage-next-event-number">?</div>
                                        <div className="homepage-next-event-label">Sekund</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            );
        }

        if (!this.state.nextEvent) {
            return (
                <>
                </>
            );
        }

        return (
            <section key="homepage-next-event" className="page__section page__section--homepage-next-event">
                <div className="page__section__content">
                    <span className="page__section__subheading">{this.state.nextEvent.name}</span>
                    <h2 className="page__section__heading">Nejbližší akce začíná už za:</h2>

                    <div className="homepage-next-event">
                        <div>
                            <div className="homepage-next-event-circle homepage-next-event-days">
                                <div className="homepage-next-event-content">
                                    <div className="homepage-next-event-number">{this.state.countdown.days}</div>
                                    <div className="homepage-next-event-label">Dní</div>
                                </div>
                            </div>
                            <div className="homepage-next-event-circle homepage-next-event-hours">
                                <div className="homepage-next-event-content">
                                    <div className="homepage-next-event-number">{this.state.countdown.hours}</div>
                                    <div className="homepage-next-event-label">Hodin</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div className="homepage-next-event-circle homepage-next-event-minutes">
                                <div className="homepage-next-event-content">
                                    <div className="homepage-next-event-number">{this.state.countdown.minutes}</div>
                                    <div className="homepage-next-event-label">Minut</div>
                                </div>
                            </div>
                            <div className="homepage-next-event-circle homepage-next-event-seconds">
                                <div className="homepage-next-event-content">
                                    <div className="homepage-next-event-number">{this.state.countdown.seconds}</div>
                                    <div className="homepage-next-event-label">Sekund</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="homepage-centered-button">
                        <Link to={this.state.nextEvent.url} onClick={(e) => {smoothScroll(this.props.refPageTop.current)}}>Přihlásit dítě</Link>
                    </div>
                </div>
            </section>
        );
    }
};

export default HomepageNextEvent
