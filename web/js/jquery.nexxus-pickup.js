/*
 * Nexxus Stock Keeping (online voorraad beheer software)
 * Copyright (C) 2018 Copiatek Scan & Computer Solution BV
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see licenses.
 *
 * Copiatek – info@copiatek.nl – Postbus 547 2501 CM Den Haag
 */

(function ( $ ) {
 
    var debug = false;
    var thisElement;
    
    $.fn.nexxusPickup = function( options ) {
 
        // Default options.
        var settings = $.extend({
            recaptchaKey: '6LdzW4QUAAAAANRAfkgl8Cz4-QNUcNEJomOj5wgX',
            orderStatusName: "To plan and pickup",
            locationId: 1,
            maxAddresses: 10,
            origin: null
        }, options);

        thisElement = $(this);
 
	    $.ajax({
            url: getMyUrl() + '/public/pickup',
            data: settings,
            type: 'GET',
            success: function (data) {
                thisElement.html(data);
                loadScriptsAndStyles();
                changeCountAddresses(0);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                thisElement.html("Error, please check your browser console.");
                console.log(jqXHR);
            }
        });
    };

    function getMyUrl() {
        var scripts = Array.from(document.getElementsByTagName("script"));
        var src = scripts.find(function(script) {
            return script.src.endsWith("jquery.nexxus-pickup.js");
          }).src;
        var url = src.substring(0, src.lastIndexOf('/'));
        
        if (debug) {
            url += '/../app_dev.php';
        }
        else {
            url += '/..';
        }

        return url;
    }

    function loadScriptsAndStyles() {

        if (!bootstrapEnabled()) {
            loadCSS("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css");
            $.getScript("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js", function() {
                    thisElement.find('[data-toggle="tooltip"]').tooltip();
                });
        }
        else {
            thisElement.find('[data-toggle="tooltip"]').tooltip();
        }

        loadCSS(getMyUrl().replace('/app_dev.php', '') + "/stylesheet/uploadifive.css");

        $.getScript(getMyUrl().replace('/app_dev.php', '') + "/js/jquery.uploadifive.min.js", function() {

            $('#pickup_form_imagesInput').uploadifive({
                'checkScript': getMyUrl() + '/uploadexists',
                'formData': {},
                'uploadScript': getMyUrl() + '/upload',
                'multi': true,
                'onUploadComplete': function (file, data) {
                    if (data.substring(0, 5) == 'Error') {
                        alert(data)
                    }
                    else {
                        $('#pickup_form_imagesNames').val($('#pickup_form_imagesNames').val() + ',' + data);
                    }
                }
            });
        
            $('#pickup_form_agreementInput').uploadifive({
                'checkScript': getMyUrl() + '/uploadexists',
                'formData': {},
                'uploadScript': getMyUrl() + '/upload',
                'multi': false,
                'onUploadComplete': function (file, data) {
                    if (data.substring(0, 5) == 'Error') {
                        alert(data)
                    }
                    else {
                        $('#pickup_form_agreementName').val(data);
                    }
                }
            }); 
        });

        thisElement.find("form").submit(function (e) {

            var form = $(this);
    
            $.ajax({
                type: "POST",
                url: getMyUrl() + '/public/pickup/post',
                data: form.serialize()
            })
            .done(function (response) {
                thisElement.html(response);
            })
            .fail(function (xhr, err) {
                thisElement.find("#errorContainer").text(xhr.responseText);   
            });
    
            e.preventDefault(); 
            e.stopPropagation();
    
            return false;
        });  

        thisElement.find("#pickup_form_countAddresses").change(function () {
            if (parseInt($(this).val()) > parseInt(thisElement.find("#pickup_form_maxAddresses").val())) {
                $(this).val(thisElement.find("#pickup_form_maxAddresses").val());
            }
            changeCountAddresses($(this).val());
        });
    }

    function changeCountAddresses(cnt) {
        
        thisElement.find('.pickup_form_address').closest('.form-group').hide();

        if (cnt > 0) {
            for (i = 1; i <= cnt; i++) {
                thisElement.find('#pickup_form_address' + i).closest('.form-group').show();
            }
        }

        thisElement.find('.pickup_form_quantity').closest('td').hide();
        thisElement.find('.pickup_form_quantity_1').closest('td').show();

        if (cnt > 0) {
            for (i = 1; i <= cnt; i++) {
                thisElement.find('.pickup_form_quantity_' + i).closest('td').show();
            }
        }        
    }

    function loadCSS(href) {
    
        var cssLink = $("<link>");
        $("head").append(cssLink); //IE hack: append before setting href

        cssLink.attr({
            rel:  "stylesheet",
            type: "text/css",
            href: href
        });

    };

    function bootstrapEnabled() {
        return (typeof $().emulateTransitionEnd == 'function');
    }
 
}( jQuery )); 