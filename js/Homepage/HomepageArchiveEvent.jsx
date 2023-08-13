import React from 'react'
import {
    Link,
    useHistory
} from "react-router-dom";
import smoothScroll from '../Helper/SmoothScroll';


const HomepageArchiveEvent = (props) => {
    let history = useHistory();

    return (
        <div key={props.archiveEvent.id} className="homepage-event">
            <Link to={props.archiveEvent.url} onClick={(e) => {smoothScroll(props.refPageTop.current)}}><img className="homepage-event-banner" src={props.archiveEvent.banner} alt={props.archiveEvent.name} loading="lazy"/></Link>
            <div className="homepage-event-content">
                <div>
                    <h3><Link to={props.archiveEvent.url} onClick={(e) => {smoothScroll(props.refPageTop.current)}}>{props.archiveEvent.name}</Link></h3>
                    <div className="homepage-event-text">
                        {props.archiveEvent.description}
                    </div>
                </div>
                <div className="homepage-event-bottom">
                    <div className="homepage-event-archive-button-wrapper">
                        <Link to={props.archiveEvent.url} onClick={(e) => {smoothScroll(props.refPageTop.current)}} className="homepage-event-archive-button">Zobrazit</Link>
                    </div>
                </div>
            </div>
        </div>
    )
};

export default HomepageArchiveEvent
