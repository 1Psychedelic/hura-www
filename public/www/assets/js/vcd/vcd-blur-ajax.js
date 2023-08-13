$(document).ready(function() {

    $(document).on('blur', '.vcd-blur-ajax', function() {
        var $el = $(this);
        var url = $el.data('ajax-url');
        var showSpinner = false;

        if($el.data('ajax-value-placeholder')) {
            var placeholder = $el.data('ajax-value-placeholder');
            url = url.replace(placeholder, $el.val());
        }

        if($el.data('ajax-show-spinner')) {
            showSpinner = true;
        }

        if(showSpinner) {
            $el.inputSpinner("show");
        }

        $.nette.ajax({
            url: url,
            complete: function() {
                $el.inputSpinner("hide");
            }
        });
    });

});
