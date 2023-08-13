import { useEffect } from "react";
import { useLocation } from "react-router-dom";
import ScrollManager from "./ScrollManager";
import React from "react";
import smoothScroll from "../Helper/SmoothScroll";

const ScrollManagerWrapper = React.forwardRef((props, ref) => {

    var keys = [];

    var menuTrigger = document.getElementById('mobile-menu-trigger');
    if (menuTrigger) {
        menuTrigger.checked = false
    }

    const location = useLocation();
    console.log({'scrollManager': props, 'ref': ref});

    //if (!keys.includes(location.key)) {
    //    keys.push(location.key);
    setTimeout(() => {
        console.log({
            'ref.current.scrollTarget': ref.current && ref.current.scrollTarget ? ref.current.scrollTarget : null,
            'ref.current.scrollTarget.current': ref.current && ref.current.scrollTarget ? ref.current.scrollTarget.current : null
        });
        if (ref.current && ref.current.scrollTarget && ref.current.scrollTarget.current) {
            smoothScroll(ref.current.scrollTarget.current)
                .finally(() => {
                    if (ref.current) {
                        ref.current.setScrollTarget(null);
                    }
                });
        }
    }, 50);
    //}

    return (
        <ScrollManager
            ref={ref}
        />
    );
});

export default ScrollManagerWrapper;

/*export default function ScrollToTop() {
    const location = useLocation();
    const pathname = location.pathname;

    const keys = [];

    useEffect(() => {
        document.getElementById('mobile-menu-trigger').checked = false;
        window.addEventListener('popstate', () => {
            console.log('popstate');
        });
        if (window.scrollY > 375) {
            window.scrollTo(0, 150);
        }
    }, [pathname]);

    return null;
}
*/
