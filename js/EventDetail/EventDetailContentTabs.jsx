import React from 'react'
import EventDetailContentTab from "./EventDetailContentTab";


class EventDetailContentTabs extends React.Component {

    constructor(props) {
        super(props);

        this.refTabs = React.createRef();
    }

    createTab(tab, event) {
        return (
            <EventDetailContentTab
                event={event}
                tab={tab}
                key={tab.slug}
                tabs={this.refTabs}
            />
        );
    }

    createTabs(tabs, event) {
        var component = this;
        return tabs.map(function (tab) {
            return component.createTab(tab, event);
        });
    }

    render() {
        if (!this.props.event.content || this.props.event.content.length <= 1) {
            return null;
        }

        return (
            <nav ref={this.refTabs} className="event-detail-content-tabs">
                <ul>
                    {this.createTabs(this.props.event.content, this.props.event)}
                    <li/>
                </ul>
            </nav>
        )
    }
};

export default EventDetailContentTabs
