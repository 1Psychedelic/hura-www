import React from 'react'

import HomepageArchiveEvent from "./HomepageArchiveEvent";


class HomepageArchiveEvents extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            'archiveEvents': props.archiveEvents || []
        };

        this.createHomepageArchiveEvent = this.createHomepageArchiveEvent.bind(this);
    }

    createHomepageArchiveEvent(archiveEvent) {
        return <HomepageArchiveEvent
            archiveEvent={archiveEvent}
            key={archiveEvent.id}
            refPageTop={this.props.refPageTop}
        />;
    }

    createHomepageArchiveEvents(archiveEvents) {
        return archiveEvents.map(this.createHomepageArchiveEvent);
    }

    static getDerivedStateFromProps(props, state) {
        if (props.archiveEvents !== state.archiveEvents) {
            return {
                'archiveEvents': props.archiveEvents
            };
        }

        return null;
    }

    render() {
        return (

            <section className="page__section page__section--homepage-archive">
                <div className="page__section__content">
                    <span
                        className="page__section__subheading">Přečtěte si, jak jsme se bavili na posledních táborech</span>
                    <h2 className="page__section__heading">Příběhy z táborů</h2>

                    <div className="homepage-events">
                        {this.createHomepageArchiveEvents(this.state.archiveEvents)}
                    </div>
                </div>
            </section>
        )
    }
};

export default HomepageArchiveEvents
