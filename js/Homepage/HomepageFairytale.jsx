import React from 'react'


const HomepageFairytale = (props) => {
    return (
        <div key={props.fairytale.id} className="homepage-fairytale">
            <img className="homepage-fairytale-banner" src={props.fairytale.banner} alt={props.fairytale.name} loading="lazy"/>
            <div className="homepage-fairytale-text">
                <h3>{props.fairytale.name}</h3>
                {props.fairytale.description}
            </div>
        </div>
    )
};

export default HomepageFairytale
