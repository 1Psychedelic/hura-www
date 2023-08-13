var FormHelper = (function () {
    return {
        convertSelectValueToBoolean: function (selectValue) {
            if (selectValue === '1') {
                return true;
            }
            if (selectValue === '0') {
                return false;
            }

            return undefined;
        },

        convertBooleanToSelectValue: function (booleanValue) {
            if (booleanValue === true) {
                return '1';
            }
            if (booleanValue === false) {
                return '0';
            }

            return '';
        }
    }
})();

export default FormHelper;
