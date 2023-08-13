
$(document).ready(function() {

    $('[data-toggle="tooltip"]').tooltip();

    $('[data-toggle="popover"]').popover();

    $(".chosen-select").chosen({
        search_contains: true,
        no_results_text: 'Žádné výsledky pro'
    });

    $('.table-fixed').floatThead({zIndex: 999});

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

    $.nette.init();

});

var fucking_eu_config = {
    "l18n": {
        "link": "/stranka/cookies"
    }
};
