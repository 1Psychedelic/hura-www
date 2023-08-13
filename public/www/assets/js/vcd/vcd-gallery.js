(function($) {

    $.fn.vcdGallery = function(options) {

        var container = $(this);

        var settings = {
            images: [],
            thumbnails: [],
            previous: null,
            current: null,
            next: null
        };

        var refresh = function(scroll) {
            if(settings.current) {

                container.find('.vcd-gallery-image').attr('src', settings.images[settings.current]);

                container.find('.vcd-thumbnail-active').removeClass('vcd-thumbnail-active');
                var currentThumbnail = container.find('img[src$=\'' + settings.thumbnails[settings.current] + '\']');
                currentThumbnail.addClass('vcd-thumbnail-active');

                if(settings.previous) {
                    $('#vcd-gallery-previous').attr('href', settings.previous);
                    $('#vcd-gallery-previous').show();
                } else {
                    $('#vcd-gallery-previous').hide();
                }
                if(settings.next) {
                    $('#vcd-gallery-next').attr('href', settings.next);
                    $('#vcd-gallery-next').show();
                    $('#vcd-gallery-middle').attr('href', settings.next);
                } else {
                    $('#vcd-gallery-next').hide();
                    $('#vcd-gallery-middle').attr('href', '#');
                }

                if(scroll) {
                    $('.vcd-thumbnails').stop(true);
                    $('.vcd-thumbnails').scrollTo($('.vcd-thumbnail-active'), 400, {offset: -100});
                }

                window.history.replaceState(null, null, settings.current);
            }
        };

        var goto = function(key, scroll) {

            var previous, next = null;
            var grabNext = false;
            var tmpPrevious = null;
            for(var i in settings.images) {
                if(grabNext) {
                    next = i;
                    grabNext = false;
                } else if(i === key) {
                    previous = tmpPrevious;
                    grabNext = true;
                }
                tmpPrevious = i;
            }

            settings.current = key;
            settings.next = next;
            settings.previous = previous;

            refresh(scroll);
        };

        if(typeof options === 'string') {
            goto(options);
        } else {

            settings = $.extend(settings, options);

            for(var i=0;i<10;i++) {
                $('.vcd-thumbnails').scrollTo($('.vcd-thumbnail-active'), 0, {offset: -100});
            }

            container.on('swiperight', '#vcd-gallery-middle', function(e) {
                if(settings.previous) {
                    goto(settings.previous, true);
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
            container.on('swipeleft', '#vcd-gallery-middle', function(e) {
                if(settings.next) {
                    goto(settings.next, true);
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
            container.on('click', '#vcd-gallery-middle, #vcd-gallery-next', function(e) {
                if(settings.next) {
                    goto(settings.next, true);
                    e.stopPropagation();
                    e.preventDefault();
                    return false;
                }
            });
            container.on('click', '#vcd-gallery-previous', function(e) {
                if(settings.previous) {
                    goto(settings.previous, true);
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
            container.on('click', '.vcd-thumbnail-link', function(e) {
                goto($(this).attr('href').split('#')[0], true);
                e.stopPropagation();
                e.preventDefault();
            });
            $(window).on('keydown', function(e) {
                if(e.keyCode === 37 || e.keyCode === 39) {
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
            $(window).on('keyup', function(e) {
                if(e.keyCode === 37 && settings.previous) {
                    goto(settings.previous, true);
                } else if(e.keyCode === 39 && settings.next) {
                    goto(settings.next, true);
                }
            });
        }
    };

}(jQuery));
