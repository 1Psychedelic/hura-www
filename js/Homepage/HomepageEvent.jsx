import React from 'react'
import {Link} from "react-router-dom";
import smoothScroll from "../Helper/SmoothScroll";


const HomepageEvent = (props) => {

    let createButton = () => {
        if (props.event.hasOpenApplications) {
            return (
                <Link to={props.event.url + '/rezervace'} onClick={() => {props.scrollManager.current.setScrollTarget(props.reservationFormTop)}}>Rezervovat</Link>
            );

        }

        return (
            <Link to={props.event.url} onClick={(e) => {smoothScroll(props.refPageTop.current)}}>Zobrazit</Link>
        );
    };

    return (
        <div key={props.event.id} className="homepage-event">
            <Link to={props.event.url} onClick={(e) => {smoothScroll(props.refPageTop.current)}}><img className="homepage-event-banner" src={props.event.banner} alt={props.event.name}/></Link>
            <div className="homepage-event-content">
                <div>
                    <h2><Link to={props.event.url} onClick={(e) => {smoothScroll(props.refPageTop.current)}}>{props.event.name}</Link></h2>
                    <div className="homepage-event-text">
                        {props.event.description}
                    </div>
                </div>
                <div className="homepage-event-bottom">
                    <div className="homepage-event-info">
                        <div className="homepage-event-info-details">
                            <span>Datum:</span> <span>{props.event.date}</span>
                        </div>
                        <div className="homepage-event-info-details">
                            <span>Věk:</span> <span>{props.event.age}</span>
                        </div>
                    </div>
                    <div className="homepage-event-price-button">
                        <div className="homepage-event-price">
                            {props.event.priceText}
                        </div>
                        <div className="homepage-event-button">
                            {createButton()}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
};

export default HomepageEvent
