import { useEffect } from "react";
import { useLocation } from "react-router-dom";

var ScrollToTop = (function () {

    var keys = [];

    return function (props) {
        //document.getElementById('mobile-menu-trigger').checked = false;

        const location = useLocation();
        console.log(props);

        if (keys.includes(location.key)) {
            return null;
        }

        keys.push(location.key);

        if (window.scrollY > 375) {
            window.scrollTo(0, 188);
        }

        return null;
    }
})();

export default ScrollToTop;

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
