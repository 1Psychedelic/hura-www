import React from 'react'
import { useEffect } from "react";
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link,
    useParams,
    useRouteMatch
} from "react-router-dom";
import EventDetailContentTabs from "./EventDetailContentTabs";

const EventDetailContent = (props) => {
    let { path, url } = useRouteMatch();

    let content = '';
    for (let i in props.event.content) {
        let tabPath = props.event.url + (props.event.content[i].slug === '' ? '' : '/' + props.event.content[i].slug);
        if (tabPath === url) {
            content = props.event.content[i].content;
            break;
        }
    }

    let renderImage= (image) => {
        return (
            <a key={image.name} href={image.image} target="_blank">
                <img src={image.thumbnail} alt={image.name} />
            </a>
        );
    };

    let renderImages = (images) => {
        return images.map(renderImage);
    };

    return (
        <div className="event-detail-content">
            <h2>{props.event.name}</h2>
            <EventDetailContentTabs
                event={props.event}
            />
            <div className="event-detail-content-text" dangerouslySetInnerHTML={{__html: content}}/>
            <div className="event-detail-content-images">
                {renderImages(props.event.images)}
            </div>
        </div>
    )
};

export default EventDetailContent
