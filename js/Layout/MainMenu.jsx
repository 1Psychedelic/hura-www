import React from 'react'
import { HashLink as Link } from 'react-router-hash-link';

const MainMenu = (props) => {
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
        <header className="page__section page__section--main-menu">
            <div className="page__section__content">
                <div className="logo">
                    <Link to="/"><img src="/images/main-menu-logo.svg" alt="Hurá tábory, z.s."/></Link>
                </div>
                <div className="main-menu">
                    <nav>
                        <ul>
                            {renderMenuItems()}
                        </ul>
                    </nav>
                </div>
            </div>
        </header>
    )
};

export default MainMenu
