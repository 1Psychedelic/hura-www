$(document).ready(function() {
    $('.chosen-select').chosen({
        search_contains: true,
        no_results_text: 'Žádné výsledky pro'
    });
    $(".select2").select2({
        theme: "bootstrap",
        width: '100%',
        createTag: function(newTag) {
            return {
                id: 'new:' + newTag.term,
                text: newTag.term
            };
        }
    });
    $(".select2").on("change", function(e) {
        var selected = $(this).select2().find(":selected");
        if(selected.length === 0)
            return;
        var data = selected.data();
        var form = this.form;
        var mode;
        var name;
        var val;
        for(var i in data) {
            if(i === 'replace') {
                mode = 'replace';
            } else if(i === 'fill') {
                mode = 'fill';
            } else {
                continue;
            }

            for(var name in data[i]) {
                val = data[i][name];
                var input = $(form).find("input[name=" + name + "], select[name=" + name + "],textarea[name=" + name + "]");
                if(input.length === 0)
                    continue;
                if(input.is(':checkbox') && mode === 'replace') {
                    input.prop("checked", val);
                } else if(input.is(':radio') && mode === 'replace') {
                    input.each(function() { // todo: fill mode for radios
                        var radio = $(this);
                        if(radio.prop("value") === val) {
                            radio.prop("checked", true);
                        } else {
                            radio.prop("checked", false);
                        }
                    });
                } else {
                    if(mode === 'replace' || !input.val()) {
                        input.val(val);
                    }
                }
                input.closest('.form-group').addClass('has-success');
                input.on('focus', function() {
                    $(this).closest('.form-group').removeClass('has-success');
                });
            }

        }
    });
    $('.datetime-picker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        stepping: 5,
        useCurrent: false,
        /*collapse: false,
         sideBySide: true,*/
        calendarWeeks: true,
        showTodayButton: true,
        showClear: true,
        showClose: true,
        toolbarPlacement: 'top'
    });
    CKEDITOR.on('dialogDefinition', function (ev) {
        // Take the dialog name and its definition from the event data.
        var dialogName = ev.data.name;
        var dialogDefinition = ev.data.definition;

        // Check if the definition is image dialog window
        if (dialogName == 'image') {
            // Get a reference to the "Advanced" tab.
            var advanced = dialogDefinition.getContents('advanced');

            // Set the default value CSS class
            var styles = advanced.get('txtGenClass');
            styles['default'] = 'img-responsive';
        }
    });
    $('.editor').each(function() {
        CKEDITOR.replace(this, {
            bodyClass: 'vcd-content',
            contentsCss: CKEDITOR.getUrl('../../css.min.css'),
            removePlugins: 'save,print',
            removeButtons: 'Source',
            disallowedContent: 'img{width,height}[width,height];',
            disableNativeSpellChecker: false
        });
    });

    // fieldset focus background color
    $('input, label, select, option, button, textarea', 'fieldset').each(function (index, item) {
        $(this).focus(function () { $(this).closest('fieldset').addClass('fieldset-focus'); });
        $(this).blur(function () { $(this).closest('fieldset').removeClass('fieldset-focus'); });
    });

    // input type=number disable scroll to change value
    $('form').on('focus', 'input[type=number]', function (e) {
        $(this).on('mousewheel.disableScroll', function (e) {
            e.preventDefault();
        });
    });
    $('form').on('blur', 'input[type=number]', function (e) {
        $(this).off('mousewheel.disableScroll');
    });
});

