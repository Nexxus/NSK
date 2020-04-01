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

var debug = false;

(function ( $ ) {
 
    $.fn.nexxusPickup = function( options ) {
 
        // Default options.
        var settings = $.extend({
            recaptchaKey: '6LdzW4QUAAAAANRAfkgl8Cz4-QNUcNEJomOj5wgX',
            orderStatusName: "To plan and pickup"
        }, options);

        var thisElement = $(this);
 
	    $.ajax({
            url: getMyUrl() + '/public/pickup',
            data: settings,
            type: 'GET',
            success: function (data) {
                thisElement.html(data);
                setTimeout(documentRealReady, 500);
                
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

    function documentRealReady() {
 
        $("form[name='pickup_form']").submit(function (e) {

            var form = $("form[name='pickup_form']");
    
            $.ajax({
                type: "POST",
                url: getMyUrl() + '/public/pickup/post',
                data: form.serialize()
            })
            .done(function (response) {
                alert(response);
            })
            .fail(function (xhr, err) {
                $("#errorContainer").text(xhr.responseText);   
            });
    
            e.preventDefault(); 
            e.stopPropagation();
    
            return false;
        });
    
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
    }
 
}( jQuery )); 