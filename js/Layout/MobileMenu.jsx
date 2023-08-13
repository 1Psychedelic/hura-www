import React from 'react'
import { HashLink as Link } from 'react-router-hash-link';

const MobileMenu = (props) => {
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
        <>
            <input type="checkbox" id="mobile-menu-trigger" className="mobile-menu-trigger" />
            <label htmlFor="mobile-menu-trigger" />
            <div className="mobile-menu">
                <nav>
                    <ul>
                        {renderMenuItems()}
                    </ul>
                </nav>
            </div>
        </>
    )
};

export default MobileMenu
