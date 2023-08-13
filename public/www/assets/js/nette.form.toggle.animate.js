$(document).ready(function() {

    var originalFunction = Nette.toggle;
    var useOriginal = true;
    var timeout;
    var states = [];
    var animateToggle = function (id, visible) {
        if(useOriginal) {
            originalFunction(id, visible);
        } else {
            var el = $('#' + id);
            var animate = true;
            if(id in states) {
                if(states[id] === visible) {
                    animate = false;
                }
            } else {
                states[id] = visible;
            }
            if(animate) {
                if (visible) {
                    states[id] = visible;
                    el.finish().slideDown(250);
                } else {
                    states[id] = visible;
                    el.finish().slideUp(250);
                }
            } else {
                console.log('skip');
            }
        }
    };
    Nette.toggle = animateToggle;
    var setToggleAnimation = function() {
        timeout = setTimeout(function() {
            useOriginal = false;
        }, 1000);
    };

    $.nette.ext('reinitAnimateToggle', {
        start: function() {
            states = [];
            clearTimeout(timeout);
            useOriginal = true;
        },
        success: function(payload) {
            states = [];
            clearTimeout(timeout);
            setToggleAnimation();
        }
    });
    setToggleAnimation();

});
