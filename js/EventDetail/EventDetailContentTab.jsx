import React from 'react'
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link,
    useParams,
    useRouteMatch,
    withRouter,
    useHistory
} from "react-router-dom";
import smoothScrollLinkClick from "../Helper/SmoothScrollLinkClick";

const EventDetailContentTab = (props) => {
    let { path, url } = useRouteMatch();

    let destination = props.event.url + (props.tab.slug === '' ? '' : '/' + props.tab.slug);

    if (url === destination) {
        return (
            <li key={props.tab.slug} className="active">
                <Link to={destination}>{props.tab.tab}</Link>
            </li>
        )
    }

    return (
        <li key={props.tab.slug}>
            <Link to={destination}>{props.tab.tab}</Link>
        </li>
    )
};

export default EventDetailContentTab
