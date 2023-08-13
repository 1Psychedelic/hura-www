$(document).ready(function() {
    if(!$(".vcd-event-box-discount").size()) {
        return;
    }

    var tickDiscount = function() {
        $(".vcd-event-box-discount").each(function() {
            var discountBox = $(this);
            var discountedUntil = moment(new Date(discountBox.data("until")));
            var now = moment();

            var diffDays = discountedUntil.diff(now, "days");
            var diffHours = discountedUntil.diff(now, "hours");
            var diffMinutes = discountedUntil.diff(now, "minutes");
            var diffSeconds = discountedUntil.diff(now, "seconds");
            var expired = diffDays < 0 || diffHours < 0 || diffMinutes < 0;
            if(expired) {
                discountBox.text("00:00");
                return;
            }
            diffSeconds = diffMinutes * 60;
            diffMinutes = diffMinutes - (diffHours * 60);
            diffHours = diffHours - (diffDays * 24);

            var str = "";
            if(diffDays > 0) {
                str += diffDays + " ";
                if(diffDays === 1) {
                    str += "den";
                } else if(diffDays > 1 && diffDays < 5) {
                    str += "dny";
                } else {
                    str += "dní";
                }
            } else {
                str += ((diffHours + "").length === 1 ? "0" : "") + diffHours + ":";
                str += ((diffMinutes + "").length === 1 ? "0" : "") + diffMinutes;
            }

            discountBox.text(str);
        });
    };
    setInterval(tickDiscount, 1000);
    tickDiscount();

});