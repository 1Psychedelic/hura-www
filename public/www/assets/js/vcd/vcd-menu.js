
$(document).ready(function() {
    $('.vcd-menu').offcanvasMenu({
        triggerId: 'vcd-menu-trigger',
        wrapper: '.vcd-wrapper',
        ignore: [
            '#vcd-carousel',
            '.vcd-box-item img',
            '.vcd-gallery'
        ]
    });
});

