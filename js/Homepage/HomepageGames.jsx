import React from 'react'
import HomepageReview from "./HomepageReview";
import HomepageGame from "./HomepageGame";
import {Link} from "react-router-dom";
import smoothScroll from "../Helper/SmoothScroll";


class HomepageGames extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            'games': props.games || []
        };
    }

    createHomepageGame(game) {
        return <HomepageGame game={game} key={game.id}/>;
    }

    createHomepageGames(games) {
        return games.map(this.createHomepageGame);
    }

    static getDerivedStateFromProps(props, state) {
        if (props.games !== state.games) {
            return {
                'games': props.games
            };
        }

        return null;
    }

    render() {
        return (
            <section key="homepage-games" className="page__section page__section--homepage-games">
                <div className="page__section__content">
                    <span className="page__section__subheading">Pro děti tvoříme vlastní tematické hry</span>
                    <h2 className="page__section__heading">Naše stolní hry</h2>

                    <div className="homepage-events">
                        {this.createHomepageGames(this.state.games)}
                    </div>

                    <div className="homepage-centered-button">
                        <Link to="/nase-stolni-hry" onClick={(e) => {smoothScroll(this.props.refPageTop.current)}}>Zobrazit všechny hry</Link>
                    </div>

                </div>
            </section>
        )
    }
};

export default HomepageGames
