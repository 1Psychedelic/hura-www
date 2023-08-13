import React from 'react'
import {Link} from "react-router-dom";


const EventDetailSidebarEvent = (props) => {
    return (
        <Link to={props.event.url}>
            <div className="event-detail-sidebar-event">
                <img src={props.event.banner} className="event-detail-sidebar-event-banner" alt={props.event.name}/>
                <div className="event-detail-sidebar-event-content">
                    <div className="event-detail-sidebar-event-name">
                        {props.event.name}
                    </div>
                    <div className="event-detail-sidebar-event-date">
                        Datum: <span className="event-detail-sidebar-event-date-value">{props.event.date}</span>
                    </div>
                </div>
            </div>
        </Link>
    )
};

export default EventDetailSidebarEvent
