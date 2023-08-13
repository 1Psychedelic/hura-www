import React from 'react'
import HomepageSubscribe from "../Homepage/HomepageSubscribe";
import {Link} from "react-router-dom";


const Footer = (props) => {

    let renderFacebook = () => {
        if (!props.website.facebookLink) {
            return (
                <>
                </>
            );
        }

        return (
            <li><a href={props.website.facebookLink} target="_blank"><img src="/images/icons/footer/icon-facebook.png" alt="Facebook logo"/> <span>Facebook</span></a></li>
        )
    };

    let renderInstagram = () => {
        if (!props.website.instagramLink) {
            return (
                <>
                </>
            );
        }

        return (
            <li><a href={props.website.instagramLink} target="_blank"><img src="/images/icons/footer/icon-instagram.png" alt="Instagram logo"/> <span>Instagram</span></a></li>
        )
    };

    let renderPinterest = () => {
        if (!props.website.pinterestLink) {
            return (
                <>
                </>
            );
        }

        return (
            <li><a href={props.website.pinterestLink} target="_blank"><img src="/images/icons/footer/icon-pinterest.png" alt="Pinterest logo"/> <span>Pinterest</span></a></li>
        )
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
                <Link to={item.url}><span>{item.text}</span></Link>
            </li>
        );
    };

    let renderMenuItems = () => {
        return props.menu.map(renderMenuItem);
    };

    return (
        <footer className="page__section page__section--footer">
            <div className="page__section__content">
                <div className="footer-arrow"/>

                <div className="footer">
                    <div className="footer-logo"/>

                    <div className="footer-content">
                        <div className="footer-content-column">
                            <div className="footer-content-column-heading">
                                Sledujte nás
                            </div>
                            <div className="footer-content-column-links">
                                <ul>
                                    {renderFacebook()}
                                    {renderInstagram()}
                                    {renderPinterest()}
                                </ul>
                            </div>
                        </div>
                        <div className="footer-content-column">
                            <div className="footer-content-column-heading">
                                Kontakty
                            </div>
                            <div className="footer-content-column-links">
                                <ul>
                                    <li><a href={'tel:' + props.website.phone}><img src="/images/icons/icon-phone.png" alt="Telefon"/> <span>{props.website.phoneHumanReadable}</span></a></li>
                                    <li><a href={'mailto:' + props.website.email}><img src="/images/icons/icon-email.png" alt="E-mail"/> <span>{props.website.email}</span></a></li>
                                    <li><a href={props.website.addressLink} target="_blank"><img src="/images/icons/footer/icon-map.png" alt="Mapa"/> <span>{props.website.address}</span></a></li>
                                </ul>
                            </div>
                        </div>
                        <div className="footer-content-column">
                            <div className="footer-content-column-heading">
                                Odkazy
                            </div>
                            <div className="footer-content-column-links-condensed">
                                <ul>
                                    {renderMenuItems()}
                                    <li><a href={props.website.rules} target="_blank"><span>Jak to u nás chodí</span></a></li>
                                    <li><a href={props.website.gdpr} target="_blank"><span>GDPR</span></a></li>
                                    <li><a href={props.website.termsAndConditions} target="_blank"><span>VOP</span></a></li>
                                </ul>
                            </div>
                        </div>
                        <div className="footer-content-column">
                            <div className="footer-content-column-heading">
                                Odběr novinek
                            </div>
                            <div className="footer-content-column-subscribe">
                                <HomepageSubscribe
                                    api={props.api}
                                    addAlert={props.addAlert}
                                    context="footer"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="footer-copyright">
                    Copyright &copy; 2021 {props.website.name}
                </div>

            </div>
        </footer>
    )
};

export default Footer
