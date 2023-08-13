Nette.showFormErrors = function(form, errors) {

    var focused = false;

    for (var i = 0; i < errors.length; i++) {
        var elem = errors[i].element,
            message = errors[i].message;



        var hbClass = '.alert';
        if($(elem).is(':radio') && $(elem).closest('.vcd-radio-group').length) {
            if (elem.focus && !focused) {
                elem.focus();
                focused = true;
            }
            elem = $(elem).closest('.vcd-radio-group').children().last().get(0);
        } else {
            if (elem.focus && !focused) {
                elem.focus();
                focused = true;
            }
        }
        if (message) {
            $(elem).closest('.form-group').addClass('has-error');
            var helpBlock = $(elem).parent().find(hbClass);
            if(helpBlock.length) {
                helpBlock.fadeTo(70, 0);
                helpBlock.fadeTo(70, 1);
            } else {
                $(elem).parent().append('<div class="alert alert-danger" style="opacity:0"><span class="glyphicon glyphicon-alert" style="font-size:0.8em;"></span> ' + message + '</div>');
                $(elem).parent().find(hbClass).fadeTo(70, 1);
            }
            $(elem).on('change, keydown', function () {
                $(this).closest('.form-group').removeClass('has-error');
                $(this).parent().find(hbClass).remove();
            });
            $(elem).on('click', function () {
                $(this).closest('.form-group').removeClass('has-error');
                $(this).parent().find(hbClass).remove();
            });
        }
    }

};

/*
Nette.addError = function (elem, message) {
    var hbClass = '.alert';
    if (elem.focus) {
        elem.focus();
    }
    if (message) {
        $(elem).closest('.form-group').addClass('has-error');
        var helpBlock = $(elem).parent().find(hbClass);
        if(helpBlock.length) {
            helpBlock.fadeTo(70, 0);
            helpBlock.fadeTo(70, 1);
        } else {
            $(elem).parent().append('<div class="alert alert-danger" style="opacity:0"><span class="glyphicon glyphicon-warning-sign"></span> ' + message + '</div>');
            $(elem).parent().find(hbClass).fadeTo(70, 1);
        }
        $(elem).on('change, keydown', function () {
            $(elem).closest('.form-group').removeClass('has-error');
            $(elem).parent().find(hbClass).remove();
        });
        $(elem).on('click', function () {
            $(elem).closest('.form-group').removeClass('has-error');
            $(elem).parent().find(hbClass).remove();
        });
    }
};

*/