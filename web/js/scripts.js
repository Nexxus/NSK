
/* focusBarcodeInput */

$(document).ready(function () {
    focusBarcodeInput();
});

function focusBarcodeInput() {
    if (!$('input.focus:first').val()) {
        $('input.focus:first').focus();
    }
}