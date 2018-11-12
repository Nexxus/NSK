

$(document).ready(function () {

    $('.combobox').combobox();

    $('.multiselect').multiselect({
        maxHeight: 200,
        buttonWidth: '100%',
        buttonClass: 'btn'
    });

    focusBarcodeInput();
});

function focusBarcodeInput() {
    if (!$('input.focus:first').val()) {
        $('input.focus:first').focus();
    }
}