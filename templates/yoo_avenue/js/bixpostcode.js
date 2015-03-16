/* *
 *  Bixie Printshop
 *  bixpostcode.js
 *  Created on 16-3-2015 00:40
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2015 Bixie.nl
 *
 */

(function (addon) {
    "use strict";

    var component;

    if (window.jQuery && window.UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixpostcode", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }


}(function ($, UI) {
    "use strict";

    UI.component('bixpostcode', {

        defaults: {
            ajaxUrl: '/index.php?option=com_ajax&format=json&plugin=bix_ideal&group=system',
            debug: false,
            spinFields: ['straat', 'plaats'],
            eventFields: ['postcode', 'huisnummer', 'huisnummer_toevoeging']
        },

        init: function () {
            var $this = this;

            this.ajaxReq = false;

            this.adresFields = this.find('[type=text]');

            this.options.eventFields.forEach(function (className) {
                $this.adresFields.filter('.' + className).keyup(function () {
                    $this.lookup();
                });
            });
            this.options.spinFields.forEach(function (className) {
                var input = $this.adresFields.filter('.' + className);
                input.parent().css('position', 'relative');
                input.after($('<i class="uk-icon-spinner uk-icon-spin"></i>').css({
                    position: 'absolute',
                    right: '10px',
                    top: '10px'
                }).hide());
            });

        },
        lookup: function () {
            var $this = this, req, postcode = this.adresFields.filter('.postcode').val().replace(/\s+/, '').toUpperCase(),
                huisnummer = this.adresFields.filter('.huisnummer').val() || 0,
                huisnummer_toevoeging = this.adresFields.filter('.huisnummer_toevoeging').val();

            if (postcode.length !== 6 || !huisnummer) {
                return;
            }
            req = {
                data: {
                    postcode: postcode,
                    huisnummer: huisnummer,
                    huisnummer_toevoeging: huisnummer_toevoeging
                }
            };
            if (this.options.debug === true) {
                console.log(req);
            }
            if (this.ajaxReq !== false) { //al aan het zoeken
                this.ajaxReq.abort();
            } else {
                this.spin();
            }
            this.ajaxReq = $.getJSON(this.options.ajaxUrl, req)
                .done(function (response) {
                    var pcReturn = response.data[0];
                    if ($this.options.debug === true) {
                        console.log(response);
                    }
                    if (pcReturn && pcReturn.response.message) {
                        UI.notify({message: pcReturn.response.message, status: 'warning'});
                    }
                    if (pcReturn && pcReturn.response.street) {
                        UI.notify.closeAll();
                        $this.adresFields.filter('.postcode').val(pcReturn.response.postcode);
                        $this.adresFields.filter('.huisnummer').val(pcReturn.response.houseNumber);
                        $this.adresFields.filter('.straat').val(pcReturn.response.street);
                        $this.adresFields.filter('.plaats').val(pcReturn.response.city);
                        $this.adresFields.filter('.type').val(pcReturn.response.addressType);
                        $this.adresFields.filter('.lat').val(pcReturn.response.latitude);
                        $this.adresFields.filter('.lon').val(pcReturn.response.longitude);
                        $this.stopSpin();
                    }
                })
                .always(function (data) {
                    $this.ajaxReq = false;
                });
        },
        spin: function () {
            var $this = this;
            this.options.spinFields.forEach(function (className) {
                $this.adresFields.filter('.' + className).next().show();
            });
        },
        stopSpin: function () {
            var $this = this;
            this.options.spinFields.forEach(function (className) {
                $this.adresFields.filter('.' + className).next().hide();
            });
        }

    });

    return UI.bixpostcode;
}));

