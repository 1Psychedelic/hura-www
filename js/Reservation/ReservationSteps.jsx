import React from 'react'
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Redirect,
    Link,
    useParams,
    useLocation,
    useRouteMatch, withRouter
} from "react-router-dom";

const ReservationSteps = (props) => {
    let createLink = (link) => {
        if (link.key === props.activeLink) {
            return (
                <li key={link.key} className="active"><span>{link.title}</span></li>
            );
        }

        if (!link.isEnabled) {
            return (
                <li key={link.key} className="disabled"><span>{link.title}</span></li>
            );
        }

        return (
            <li key={link.key}>
                <Link to={link.url}><span>{link.title}</span></Link>
            </li>
        )
    };

    let createLinks = (links) => {
        return links.map(createLink);
    };

    return (
        <>
            <nav className="reservation-steps">
                <ul>
                    {createLinks(Object.values(props.links))}
                </ul>
            </nav>
        </>
    );
};

export default ReservationSteps;
