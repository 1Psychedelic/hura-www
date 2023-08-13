(function($) {

    $.fn.inputSpinner = function(options) {

        var input = $(this);
        var spinner;

        var show = function() {
            if(spinner) {
                return;
            }

            spinner = $('<div class="vcd-input-spinner"></div>');
            spinner.insertAfter(input);
        };

        var hide = function() {
            input.siblings('.vcd-input-spinner').each(function() {
                $(this).remove();
                spinner = null;
            });
        };

        if(options == "show") {
            show();
        } else if (options == "hide") {
            hide();
        }
    }

}(jQuery));
