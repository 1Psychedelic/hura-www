import React from 'react'
import { HashLink as Link } from 'react-router-hash-link';
import smoothScroll from "../Helper/SmoothScroll";


const HomepageGame = (props) => {
    return (
        <div key={props.game.id} className="homepage-event">
            <img className="homepage-event-banner" src={props.game.bannerSmall} alt={props.game.name} loading="lazy"/>
            <div className="homepage-event-content">
                <div>
                    <h3>{props.game.name}</h3>
                    <div className="homepage-event-text">
                        {props.game.descriptionShort}
                    </div>
                </div>
                <div className="homepage-event-bottom">
                    <div className="homepage-event-archive-button-wrapper">
                        <Link to={'/nase-stolni-hry#' + props.game.slug} className="homepage-event-archive-button">Více informací</Link>
                    </div>
                </div>
            </div>
        </div>
    )
};

export default HomepageGame
