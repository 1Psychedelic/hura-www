import React from 'react'
import TopMenuLogin from "./TopMenuLogin";
import { HashLink as Link } from 'react-router-hash-link';


const TopMenu = (props) => {

    let renderPhone = () => {
        if (!props.initialLoadFinished) {
            return (
                <div className="contact-info-phone ph-item">
                    <img src="/images/icons/icon-phone.png" alt="Telefon"/>
                    <div className="loading"/>
                </div>
            );
        }

        return (
            <div className="contact-info-phone">
                <img src="/images/icons/icon-phone.png" alt="Telefon"/><a href={'tel:' + props.website.phone}>{props.website.phoneHumanReadable}</a>
            </div>
        );
    };

    let renderEmail = () => {
        if (!props.initialLoadFinished) {
            return (
                <div className="contact-info-email ph-item">
                    <img src="/images/icons/icon-email.png" alt="E-mail"/><div className="loading"/>
                </div>
            );
        }

        return (
            <div className="contact-info-email">
                <img src="/images/icons/icon-email.png" alt="E-mail"/><a href={'mailto:' + props.website.email}>{props.website.email}</a>
            </div>
        );
    };

    let renderMenuItem = (item) => {
        if (item.isExternal) {
            return (
                <li key={item.id}>
                    <a href={item.url} target="_blank">{item.text}</a>
                </li>
            );
        }
        return (
            <li key={item.id}>
                <Link to={item.url}>{item.text}</Link>
            </li>
        );
    };

    let renderMenuItems = () => {
        return props.menu.map(renderMenuItem);
    };

    return (
        <header className="page-topbar page__section page__section--top-menu">
            <div className="page__section__content">
                <div className="contact-info">
                    {renderPhone()}
                    {renderEmail()}
                </div>
                <div className="top-menu">
                    <nav>
                        <ul>
                            {renderMenuItems()}
                            <TopMenuLogin
                                api={props.api}
                                authentication={props.authentication}
                                addAlert={props.addAlert}
                                googleAppId={props.googleAppId}
                                facebookAppId={props.facebookAppId}
                                notifications={props.notifications}
                            />
                        </ul>
                    </nav>
                </div>
            </div>
        </header>
    )
};

export default TopMenu
