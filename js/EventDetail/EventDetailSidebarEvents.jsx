import React from 'react'

import EventDetailSidebarEvent from "./EventDetailSidebarEvent";


class EventDetailSidebarEvents extends React.Component {

    createEventDetailSidebarEvent(event) {
        return <EventDetailSidebarEvent event={event} key={event.id}/>;
    }

    createEventDetailSidebarEvents(events) {
        return events.map(this.createEventDetailSidebarEvent);
    }

    render() {
        var component = this;
        let filteredEvents = this.props.events.filter(function (event) {
            return component.props.event.id !== event.id;
        });

        filteredEvents = filteredEvents.slice(0, 3);

        if (filteredEvents.length === 0) {
            return null;
        }

        return (
            <>
                <div className="event-detail-sidebar-heading">Další tábory:</div>
                <div className="event-detail-sidebar-events">
                    {this.createEventDetailSidebarEvents(filteredEvents)}
                </div>
            </>
        )
    }
};

export default EventDetailSidebarEvents
