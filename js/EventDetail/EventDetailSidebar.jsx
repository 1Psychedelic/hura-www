import React from 'react'
import EventDetailSidebarEvents from "./EventDetailSidebarEvents";
import HomepageSubscribe from "../Homepage/HomepageSubscribe";


const EventDetailSidebar = (props) => {
    return (
        <div className="event-detail-sidebar">

            <div className="event-detail-sidebar-section">
                <div className="event-detail-sidebar-heading">Náš Facebook:</div>

                <iframe
                    src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fhuratabory&tabs&width=256&height=70&small_header=true&adapt_container_width=true&hide_cover=false&show_facepile=true&appId=596213888157757"
                    width="256" height="70" style={{border:'none',overflow:'hidden'}} scrolling="no" frameBorder="0"
                    allowFullScreen="true"
                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                />
            </div>

            <div className="event-detail-sidebar-section">
                <div className="event-detail-sidebar-heading">Přihlašte se k odběru!</div>

                <HomepageSubscribe
                    api={props.api}
                    addAlert={props.addAlert}
                    context="event-sidebar"
                />
            </div>


            <div className="event-detail-sidebar-section">
                <EventDetailSidebarEvents event={props.event} events={props.events}/>
            </div>

            <div className="event-detail-sidebar-section">
                <div dangerouslySetInnerHTML={{__html: props.event.sidebarHtml}}/>
            </div>

            <div className="event-detail-sidebar-bottom">&nbsp;</div>
        </div>
    )
};

export default EventDetailSidebar
