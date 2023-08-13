
$(document).ready(function() {
    Nette.validators.HafoNetteBridgeFormsValidatorsCzechPersonalIdValidator_validate = function(elem, args, val) {
        var age = 0;
        var x = val.replace('/', '');
        try {
            if(x.length == 0) return true;
            if(x.length < 9) throw 1;
            var year = parseInt(x.substr(0, 2), 10);
            var month = parseInt(x.substr(2, 2), 10);
            var day = parseInt( x.substr(4, 2), 10);
            var ext = parseInt(x.substr(6, 3), 10);
            if((x.length == 9) && (year < 54)) return true;
            var c = 0;
            if(x.length == 10) c = parseInt(x.substr(9, 1));
            var m = parseInt( x.substr(0, 9)) % 11;
            if(m == 10) m = 0;
            if(m != c) throw 1;
            year += (year < 54) ? 2000 : 1900;
            if((month > 70) && (year > 2003)) month -= 70;
            else if (month > 50) month -= 50;
            else if ((month > 20) && (year > 2003)) month -= 20;
            var d = new Date();
            if((year + age) > d.getFullYear()) throw 1;
            if(month == 0) throw 1;
            if(month > 12) throw 1;
            if(day == 0) throw 1;
            if(day > 31) throw 1;
        }
        catch(e) {
            return false;
        }
        return true;
    };
});

