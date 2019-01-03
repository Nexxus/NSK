/*

Generic converter from normal anchor to modal opener
Author: Jorrit Steetskamp

*/

$(document).on("click", '.btn-modal', function (e) {
    e.preventDefault();
    var a = $(e.currentTarget);
    var target = a.data('target');
    $(target + " .modal-content").load(a.attr('href'), function () {
        $(target).modal('show');
    });
    
});