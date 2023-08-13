import smoothScroll from './SmoothScroll';

function smoothScrollLinkClick(event, elem, history, offset = -60) {
    event.preventDefault();
console.log(elem);
    const targetUrl = event.target.href.replace(window.location.origin, '');

    smoothScroll(elem, offset)
        .finally(() => {
            history.push(targetUrl);
        });
}

export default smoothScrollLinkClick;
