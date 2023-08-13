import React from 'react'
import {Link} from "react-router-dom";
import {Helmet} from "react-helmet";


const Games = (props) => {
    if (!props.initialLoadFinished) {
        return (
            <>
            </>
        );
    }

    let createGame = (item) => {
        return (
            <div className="game">
                <a id={item.slug}/>
                <img className="game-image" src={item.bannerLarge || item.bannerSmall} alt={item.name}/>
                <div className="game-description">
                    <h3>{item.name}</h3>
                    <div dangerouslySetInnerHTML={{__html: item.descriptionLong || item.descriptionShort}}/>
                </div>
            </div>
        );
    };

    let createGames = () => {
        return props.games.map(createGame);
    };

    return (
        <>
            <Helmet>
                <title>Naše deskové hry</title>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">Pro děti tvoříme tématické deskové hry, které v průběhu tábora sbírají a odnáší si je domů na památku.</span>
                    <h1 className="page__section__heading">Naše deskové hry</h1>
                </div>
            </section>
            <section key="events" className="page__section page__section--homepage-events">
                <div className="page__section__content">
                    <div className="games">
                        {createGames()}
                    </div>
                </div>
            </section>
        </>
    )
};

export default Games
