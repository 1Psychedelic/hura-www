import React from 'react'
import {Helmet} from "react-helmet";


const Error404 = (props) => {

    let title = props.title || '404';
    let heading = props.heading || '404';
    let subheading = props.subheading || 'Stránka nebyla nalezena!';

    let renderMeta = () => {
        if (title === '404') {
            return (
                <meta name="robots" content="noindex, follow"/>
            );
        }

        return (
            <>
            </>
        );
    };

    return (
        <>
            <Helmet>
                <title>{title}</title>
                {renderMeta()}
            </Helmet>
            <section className="page__section page__section--error404">
                <div className="page__section__content">
                    <h2 className="page__section__heading">{heading}</h2>
                    <span className="page__section__subheading">{subheading}</span>

                </div>
            </section>
            <div className="error404-image">
                <img src="/images/background/404.png" alt="Stránka nenalezena" />
            </div>
        </>
    )
};

export default Error404
