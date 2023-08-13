import React from 'react'

import HomepageFairytale from "./HomepageFairytale";


class HomepageFairytales extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            'fairytales': props.fairytales || []
        };
    }

    createHomepageFairytale(fairytale) {
        return <HomepageFairytale fairytale={fairytale} key={fairytale.id}/>;
    }

    createHomepageFairytales = (fairytales) => {
        return fairytales.map(this.createHomepageFairytale);
    };

    createHomepageFairytalesGroup(fairytalesGroup, index) {
        return (
            <div key={index} className="homepage-fairytales-row">
                {this.createHomepageFairytales(fairytalesGroup)}
            </div>
        )
    }

    createHomepageFairytalesGroups(fairytalesGroups) {
        return fairytalesGroups.map(this.createHomepageFairytalesGroup.bind(this));
    }

    static getDerivedStateFromProps(props, state) {
        if (props.fairytales !== state.fairytales) {
            return {
                'fairytales': props.fairytales
            };
        }

        return null;
    }

    render() {
        return (
            <section key="homepage-fairytales" className="page__section page__section--homepage-fairytales">
                <div className="page__section__content">
                    <span className="page__section__subheading">Pro děti píšeme i vlastní pohádky</span>
                    <h2 className="page__section__heading">Naše pohádky</h2>

                    <div className="homepage-fairytales">
                        {this.createHomepageFairytalesGroups(this.state.fairytales)}
                    </div>
                </div>
            </section>
        )
    }
};

export default HomepageFairytales
