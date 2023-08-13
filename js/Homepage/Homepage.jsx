import React from 'react'

import HomepageEvents from './HomepageEvents';
import HomepageWhyUs from './HomepageWhyUs';


import HomepageReviews from "./HomepageReviews";
import HomepageGames from "./HomepageGames";
import HomepageFairytales from "./HomepageFairytales";
import HomepageNextEvent from "./HomepageNextEvent";
import HomepageSubscribe from "./HomepageSubscribe";
import HomepageArchiveEvents from "./HomepageArchiveEvents";

class Homepage extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            'homepage': props.homepage || {
                'enabledSections': [],
                'events': [],
                'reviews': [],
                'fairytales': [],
                'nextEvent': null,
                'archiveEvents': []
            },
            'games': props.games || [],
        };

        this.renderWhyUs = this.renderWhyUs.bind(this);
        this.renderReviews = this.renderReviews.bind(this);
        this.renderGames = this.renderGames.bind(this);
        this.renderFairytales = this.renderFairytales.bind(this);
        this.renderNextEvent = this.renderNextEvent.bind(this);
        this.renderSubscribe = this.renderSubscribe.bind(this);
        this.renderArchiveEvents = this.renderArchiveEvents.bind(this);
    }

    static getDerivedStateFromProps(props, state) {
        if (props.homepage !== state.homepage) {
            return {
                'homepage': props.homepage
            };
        }

        return null;
    }

    renderWhyUs() {
        if (this.state.homepage.enabledSections.includes('whyUs')) {
            return (
                <HomepageWhyUs />
            )
        }

        return (
            <>
            </>
        )
    }

    renderReviews() {
        if (this.state.homepage.enabledSections.includes('reviews')) {
            return (
                <HomepageReviews reviews={this.state.homepage.reviews} />
            )
        }

        return (
            <>
            </>
        )
    }

    renderGames() {
        if (this.state.homepage.enabledSections.includes('games')) {
            return (
                <HomepageGames
                    games={this.state.games.filter((game) => {return game.isVisibleOnHomepage;})}
                    refPageTop={this.props.refPageTop}
                />
            )
        }

        return (
            <>
            </>
        )
    }

    renderFairytales() {
        if (this.state.homepage.enabledSections.includes('fairytales')) {
            return (
                <HomepageFairytales fairytales={this.state.homepage.fairytales} />
            )
        }

        return (
            <>
            </>
        )
    }

    renderNextEvent() {
        if (this.state.homepage.enabledSections.includes('nextEvent')) {
            return (
                <HomepageNextEvent
                    nextEvent={this.state.homepage.nextEvent}
                    initialLoadFinished={this.props.initialLoadFinished}
                    refPageTop={this.props.refPageTop}
                />
            )
        }

        return (
            <>
            </>
        )
    }

    renderSubscribe() {
        if (this.state.homepage.enabledSections.includes('subscribe')) {
            return (
                <HomepageSubscribe
                    api={this.props.api}
                    addAlert={this.props.addAlert}
                />
            )
        }

        return (
            <>
            </>
        )
    }

    renderArchiveEvents() {
        if (this.state.homepage.enabledSections.includes('archiveEvents')) {
            return (
                <HomepageArchiveEvents
                    archiveEvents={this.state.homepage.archiveEvents}
                    refPageTop={this.props.refPageTop}/>
            )
        }

        return (
            <>
            </>
        )
    }

    render() {
        return (
            <>
                <HomepageEvents
                    key="homepage-events"
                    events={this.state.homepage.events}
                    initialLoadFinished={this.props.initialLoadFinished}
                    scrollManager={this.props.scrollManager}
                    reservationFormTop={this.props.reservationFormTop}
                    slogan={this.props.slogan}
                    heading={this.props.heading}
                    refPageTop={this.props.refPageTop}
                />
                {this.renderWhyUs()}
                {this.renderReviews()}
                {this.renderGames()}
                {this.renderFairytales()}
                {this.renderNextEvent()}
                {this.renderSubscribe()}
                {this.renderArchiveEvents()}
            </>
        )
    }
}

export default Homepage
