function smoothScroll(elem, offset = 0) {
    const rect = elem.getBoundingClientRect();

    offset = screen.width <= 700 ? offset : 0;

    /*if (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    ) {
        console.log('Is visible already!');
        return new Promise((resolve, reject) => {
            resolve();
        });
    } else *///{
        let targetPosition = Math.floor(rect.top + window.pageYOffset + offset);

        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });

        return new Promise((resolve, reject) => {
            const failed = setTimeout(() => {
                reject();
            }, 2000);

            const scrollHandler = () => {
                console.log({'y': window.pageYOffset, 'target': targetPosition});
                if (window.pageYOffset === targetPosition) {
                    window.removeEventListener('scroll', scrollHandler);
                    clearTimeout(failed);
                    resolve();
                }
            };
            if (window.pageYOffset === targetPosition) {
                clearTimeout(failed);
                resolve();
            } else {
                window.addEventListener('scroll', scrollHandler);
            }
        });
    //}
}

export default smoothScroll;
