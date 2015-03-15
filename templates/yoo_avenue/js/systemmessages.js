/* *
 *	Bixie Printshop
 *  systemmessages.js
 *	Created on 9-3-14 16:38
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

jQuery(function($) {
    "use strict";

    (function(){
        var messageEl = $('#system-message');
        messageEl.addClass('uk-hidden');
        messageEl.find('[data-uk-alert]').each(function(){
            var message = $(this);
            showMessage(message.find('.text').html(),message.data('type'));
        });
    })();

    function showMessage(message,style) {
        $.UIkit.notify({
            message : message,
            status  : style,
            timeout : 5000,
            pos     : 'top-center'
        });
    }
});