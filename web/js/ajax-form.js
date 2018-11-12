/*

Generic converter from normal form to ajax form
Author: Jorrit Steetskamp

*/

$(document).on("submit", '.ajax-form', function (e) {
    e.preventDefault();
    var form = $(e.target);
    $.ajax({
        url: form.attr('action'),
        type: 'POST', 
        data: $(this).serialize(),
        success: function (data) {
            var target = form.attr('target');
            $(target).html(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            document.body.innerHTML = jqXHR.responseText; 
        }
    });
});