/*
 * (c)2013-2015 - Matthijs Alles - Bixie.org
 */

(function (addon) {
    "use strict";

    var component;

    if (window.UIkit) {
        component = addon(UIkit.$, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixshopform", ["uikit"], function () {
            return component || addon(UIkit.$, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    var BixZooShop, BixZooProduct;

    BixZooShop = function () {
        this.products = {};
    };
    $.extend(BixZooShop.prototype, {
        addProduct: function (id, productInfo) {
            this.products['product' + id] = productInfo;
        }
    });

    UI.bixZooShop = new BixZooShop();

    BixZooProduct = function (element, options) {
        this.element = $(element);
        this.options = $.extend(true, {}, options);
        this.productID = this.element.attr('id');
        this.data = UI.bixZooShop.products[this.productID];
        this.dataFields = this.element.find('[data-zoo-product-option]');
    };
    $.extend(BixZooProduct.prototype, {
        calculate: function () {
            var $this = this,
                prodPrice = parseFloat(this.data.price),
                prodtotalPrice = 0,
                orderedTotal = 0,
                orderedOpslag = 0,
                prodBtwFactor = this.options.btw[this.data.btw];
            $.each(this.data.optionData, function (optionName, optionValues) {
                $.each(optionValues, function (optionValue, optionData) {
                    var value = $this.dataFields.filter('[data-zoo-product-option="' + optionValue + '"]').val(),
                        price = parseFloat(optionData.price || 0),
                        intValue = parseInt(value, 10);
                    switch (optionData.type) {
                    case 'shopoptie'://untested
                        if (value && price) {
                            prodPrice += price;
                        }
                        break;
                    case 'shopaantal':
                        if (intValue > 0) {
                            orderedTotal += intValue;
                            if (price) {
                                orderedOpslag += (price * intValue);
                            }
                        }
                        break;
                    default:
                        break;
                    }

                });
            });
            if (orderedTotal > 0) {
                prodtotalPrice = prodPrice * orderedTotal + orderedOpslag;
            }
            return {
                prodPrice: prodtotalPrice,
                prodBtw: (prodtotalPrice / (100 + prodBtwFactor)) * prodBtwFactor
            };
        }
    });

    UI.component('bixshopform', {

        defaults: {
            btw: {hoog: 21, laag: 6},
            bevestigText: '<i class="uk-icon-check uk-margin-small-right"></i>Bevestig bestelling',
            incompleetText: '<i class="uk-icon-ban uk-margin-small-right"></i>Bestelling niet compleet'
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-shopform]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixshopform")) {
                        UI.bixshopform($ele, UI.Utils.options($ele.attr('data-bix-shopform')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.bixZooShop = UI.bixZooShop;
            this.producten = {};
            this.find('div.productItem').each(function () {
                $this.producten[$(this).attr('id')] = new BixZooProduct($(this), $this.options);
            });
            this.on('click', 'div.productItem input[type=checkbox], .plus, .min', function () {
                $this.calculate();
            });
            this.on('change', 'div.productItem input[type=text]', function () {
                $this.calculate();
            });
            this.on('keydown', 'input[type=checkbox]', function (e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    $this.calculate();
                }
            });
            this.postcode = UI.bixpostcode(this.find('.bix-adres'));
            this.postcode.on('response.pc.received', function () {
                $this.calculate();
            });
            this.bezorginfoEl = this.find('#bezorginfo');
            this.vervoersInfoInput = this.find('#vervoersInfoInput');
            this.verzgebiedSelect = this.find('select[name=verzgebied]').change(function (e) {
                $this.calculate();
            });
            this.$submitButton = this.find('button[type=submit]');
            this.$prijsFormat = this.find('#prijsTotaal');
            this.$btwFormat = this.find('#prijsBtw');
            this.$prijs = this.find('#prijs');
            this.$btw = this.find('#btw');

            this.calculate();
        },
        calculate: function () {
            var $this = this, verzGebied;
            this.orderValid = true;
            this.validMessage = '';
            this.btw = 0;
            this.price = 0;
            this.prices = {};
            this.btws = {};
            $.each(this.producten, function (productID, zooProduct) {
                var calc = zooProduct.calculate();
                $this.prices[productID] = calc.prodPrice;
                $this.btws[productID] = calc.prodBtw;
                $this.price += calc.prodPrice;
                $this.btw += calc.prodBtw;
            });
            verzGebied = this.check();
            this.setDom(verzGebied);
        },
        check: function () {
            var pcode = this.postcode.adresFields.filter('.postcode').val(),
                verzGebied,
                bezorgKosten;

            try {

                this.checkOrder(pcode);
                verzGebied = this.checkPostcode(pcode);

            } catch (exception) {
                if (exception instanceof UI.BixZooProductError) {
                    this.orderValid = false;
                    this.validMessage = exception.message;
                } else {
                    if (console) {
                        console.log(exception.stack);
                    }
                    throw exception;
                }
            }
            if (this.orderValid) { //kosten toevoegen
                //vervoerskosten
                switch (verzGebied) {
                case 'utwente':
                    bezorgKosten = 0;
                    break;
                case 'marssteden':
                    if (this.price < 20) {
                        bezorgKosten = 9;
                    }
                    break;
                case 'enschede':
                    if (this.price < 25) {
                        bezorgKosten = 10;
                    }
                    break;
                case 'buiten':
                    if (this.price < 50) {
                        bezorgKosten = 18;
                    }
                    break;
                case 'aanvraag':
                    //valt in !orderValid
                    break;
                }
                this.validMessage = this.verzgebiedSelect.find('[value=' + verzGebied + ']').text();
                if (bezorgKosten > 0) {
                    this.validMessage += ' Kosten: € ' + bezorgKosten + ',-.';
                    this.price += bezorgKosten;
                    this.btw += (bezorgKosten / (100 + this.options.btw.hoog)) * this.options.btw.hoog;
                }
            }
            //round prices price is BRUTO!
            this.price = Math.round(this.price * 100) / 100;
            this.btw = Math.round(this.btw * 100) / 100;

            return verzGebied;
        },
        checkOrder: function (pcode) {
            if (this.price === 0) {
                throw new UI.BixZooProductError("Selecteer eerst uw producten");
            }
            if (this.price < 10) {
                throw new UI.BixZooProductError("Minimale afname bezorging €10,00");
            }
            if (pcode === '' || !pcode.match(/^[0-9]{4}[A-Z]{2}/)) {
                throw new UI.BixZooProductError("Adres is niet valide");
            }
        },
        checkPostcode: function (pcode) {
            var verzGebied = '', codeNummers = pcode.substr(0, 4).toInt(),
                enschedeBuiten = [7524, 7525, 7532],
                UT = ['7522NB', '7522ND', '7522NH', '7522LW', '7522EA', '7521NJ', '7522LV', '7522NJ', '7522LP', '7522LV', '7522NM', '7522NL', '7522MJ', '7522MG', '7522NR', '7522NE', '7522MJ', '7522NC', '7522NR', '7521AN', '7521PA', '7521AG', '7547AN', '7511GB', '7521AG', '7522NR', '7514AE', '7513EA', '7522NH', '7522ND', '7522PB', '7522NR', '7522ND', '7521PT'];
            if (codeNummers >= 7500 && codeNummers <= 7547 && enschedeBuiten.indexOf(codeNummers) === -1) {// binnen enschede
                verzGebied = 'enschede';
                if (UT.indexOf(pcode) !== -1) { //ut
                    verzGebied = 'utwente';
                }
                if (codeNummers === 7547) { //haven,planet,twence,marsstede
                    verzGebied = 'utwente';
                    if (pcode.match(/T/)) { //marssteden
                        verzGebied = 'marssteden';
                    }
                }
            } else { //buiten enschede
                if ((codeNummers > 7547 && codeNummers <= 7558) || enschedeBuiten.indexOf(codeNummers) === -1) { //boekelo en hengelo en enschbuiten
                    verzGebied = 'buiten';
                } else { //buiten bereik
                    verzGebied = 'aanvraag';
                    throw new UI.BixZooProductError("Adressen buiten verzorgingsgebied alleen op aanvraag!");
                }
            }
            console.log(verzGebied);
            return verzGebied;
        },
        enableButton: function (state) {
            this.$submitButton.prop('disabled', !state).html(state ? this.options.bevestigText : this.options.incompleetText);
        },
        setDom: function (verzGebied) {
            this.enableButton(this.orderValid);
            this.bezorginfoEl.removeClass('uk-alert-warning').html(this.validMessage);
            if (!this.orderValid) {
                this.bezorginfoEl.addClass('uk-alert-warning');
            }
            this.verzgebiedSelect.val(verzGebied);
            this.vervoersInfoInput.val(this.validMessage);

            this.$prijs.val(this.price);
            this.$btw.val(this.btw);
            this.$prijsFormat.html('€ ' + this.price.toFixed(2).replace('.', ','));
            this.$btwFormat.html('€ ' + this.btw.toFixed(2).replace('.', ','));
        }
    });

    return UI.bixshopform;
}));

(function ($, UI) {
    "use strict";

    function BixZooProductError(message) {
        this.name = "BixZooProductError";
        this.message = (message || "");
    }

    BixZooProductError.prototype = new Error();
    BixZooProductError.prototype.constructor = BixZooProductError;

    UI.BixZooProductError = BixZooProductError;

}(jQuery, UIkit));
