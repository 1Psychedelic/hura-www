var VCD = VCD || {};

$.nette.ext('consoleLog', {
    success: function(payload) {
        console.log('Incoming payload!');
        console.log(payload);
    },
    error: function(jqXHR, status, error) {
        console.log('Ajax failed!');
        console.log(error);
        console.log(jqXHR);
    }
});

$.nette.ext('realRedirect', {
    success: function(payload) {
        if(payload.realRedirect) {
            document.location = payload.realRedirect;
        }
    },
    error: function(jqXHR, status, error) {
    }
});

$.nette.ext('replaceForm', {
    success: function(payload) {
        if(payload.formReplace) {
            for(var id in payload.formReplace) {
                val = payload.formReplace[id];
                var input = $(document).find("input#" + id + ", select#" + id + ",textarea#" + id + "");
                if(input.length === 0) {
                    continue;
                }
                if(input.is(':checkbox')) {
                    input.prop("checked", val);
                } else if(input.is(':radio')) {
                    input.each(function() { // todo: fill mode for radios
                        var radio = $(this);
                        if(radio.prop("value") === val) {
                            radio.prop("checked", true);
                        } else {
                            radio.prop("checked", false);
                        }
                    });
                } else {
                    input.val(val);
                }
                /*input.closest('.form-group').addClass('has-warning');
                input.on('focus', function() {
                    $(this).closest('.form-group').removeClass('has-warning');
                });*/
            }
        }
    },
    error: function(jqXHR, status, error) {}
});

VCD.showLogin = function() {
    if($('label[for="vcd-menu-trigger"]').is(":visible")) { // mobile menu
        $('#vcd-menu-trigger').prop('checked', true);
        if(!$('.vcd-menu .dropdown').hasClass('open')) {
            $('.vcd-menu .dropdown-toggle').dropdown('toggle');
        }
    } else { // desktop menu
        if(!$('#vcd-navbar .dropdown').hasClass('open')) {
            $('#vcd-navbar .dropdown-toggle').dropdown('toggle');
        }
        $.scrollTo('#vcd-navbar .dropdown-toggle');
    }
};

$(document).ready(function() {
    $('#vcd-login-box .vcd-login-dropdown').click(function(e){
        e.stopPropagation();
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    $(".chosen-select").chosen({
        search_contains: true,
        no_results_text: 'Žádné výsledky pro'
    });

    $.nette.init();

    $('.vcd-login-button').on('click', function(e) {
        e.stopPropagation();
        VCD.showLogin();
    });

    $('.vcd-capacity-hover').on('mouseover', function() {
        $(this).find('.vcd-capacity').show();
        $(this).find('.vcd-capacity-progress').hide();
    });
    $('.vcd-capacity-hover').on('mouseout', function() {
        $(this).find('.vcd-capacity').hide();
        $(this).find('.vcd-capacity-progress').show();
    });

    $("img[data-src]").recliner({
        attrib: "data-src",
        throttle: 300,
        threshold: 100,
        printable: true,
        live: true
    });

    $('.date-picker').datetimepicker({
        format: 'YYYY-MM-DD',
        stepping: 5,
        useCurrent: false,
        /*collapse: false,
         sideBySide: true,*/
        calendarWeeks: true,
        showTodayButton: false,
        showClear: false,
        showClose: false,
        toolbarPlacement: 'top',
        locale: 'cs',
        viewMode: 'years'
    });

    // fieldset focus background color
    $('input, label, select, option, button, textarea', 'fieldset').each(function (index, item) {
        $(this).focus(function () { $(this).closest('fieldset').addClass('fieldset-focus'); });
        $(this).blur(function () { $(this).closest('fieldset').removeClass('fieldset-focus'); });
    });

    // btn-group checkbox toggles
    $('body').on('change', '.btn-group[data-toggle="buttons"] input[type="checkbox"]', function() {
        Nette.toggleForm(this.form);
    });

});