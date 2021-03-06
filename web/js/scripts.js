﻿

$(document).ready(function () {

    $('[data-toggle="tooltip"]').tooltip({
        placement: 'auto right',
        html: true
    });

    $('.combobox').combobox();

    $('.multiselect').multiselect({
        maxHeight: 200,
        buttonWidth: '100%',
        buttonClass: 'btn'
    });

    $('#index_bulk_edit_form_action').change(function (e) {

        var form = $('form[name="index_bulk_edit_form"]');

        if ($(this).val() != "status") 
            form.attr('target', '_blank')
        else 
            form.attr('target', '')

        form.submit();
    });

    $('.btn-selectall').click(function (e) {
        $('input[name^="index_bulk_edit_form[index]"]').each(function() {
            $(this).prop('checked', true);
        });
    });

    $('.btn-delete').click(function (e) {

        e.preventDefault();

        var href = $(this).attr("href");
        var className = $(this).data("class");
        var name = $(this).data("name");

        $('#modalConfirm .modal-body').html("Are you sure you want to delete this " + className + "?<br/>&nbsp;<br/><b>" + name + "</b>" );
        $('#modalConfirm').modal('show') 

        $('.btn-delete-confirmed').click(function () {
            location.href = href;
        });
    });

    focusBarcodeInput();
});

function focusBarcodeInput() {
    //if (!$('input.focus:first').val()) {
        $('input.focus:first').select();
        $('input.focus:first').focus();
    //}
}