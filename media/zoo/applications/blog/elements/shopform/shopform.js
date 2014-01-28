/*
* bix_cart.js - javascript for Bixie Printshop cart
* (c)2011-2013 - Matthijs Alles - Bixie.org
*/


var bixZooShopClass = new Class({
	Implements: [Options],
	options: {
		btw: {hoog:21,laag:6}
	},
	form: {},
	products: {},
	productEls: {},
	verzgebiedSelect: {},
	geo: {},
	bezorginfoEl: {},
	vervoersInfoInput: {},
	initialize: function () {
		this.setOptions();
	},
	loadDom: function (formID,options) {
		this.setOptions(options);
		this.form = document.id(formID);
		var self = this;
		this.form.getElements('div.productItem').each(function (prodDiv) {
			var productID = prodDiv.get('id').replace('product','');
			self.productEls[productID] = prodDiv;
		});
		Object.each(this.productEls, function (prodDiv,productID) {
			prodDiv.getElements('input[type=checkbox]').each(function (checkbox) {
				checkbox.addEvent('click',function (e) {
					self.calculate();
				});
			});
			prodDiv.getElements('input[type=text]').each(function (input) {
				input.addEvent('change',function (e) {
					self.calculate();
				});
			});
		});
		this.form.addEvent('keydown', function(event) {
		  if(event.key == "enter") {
			self.calculate();
			return false;
		  }
		});
		this.geo = new bixGeo('userdata',{debug:true,onComplete:function(result){
			if (typeof result.returnData.street != 'undefined') {
				self.calculate();
			}
		}});
		this.bezorginfoEl = document.id('bezorginfo');
		this.vervoersInfoInput = document.id('vervoersInfoInput');
		this.verzgebiedSelect = this.form.getElement('select[name=verzgebied]');
		this.verzgebiedSelect.addEvent('change',function (e) {
			self.calculate();
		});
		this.calculate();
	},
	addProduct: function (prodID,prodData,options) {
		this.products[prodID] = new bixProduct(prodData,options);
	},
	calculate: function () {
		var data = this.form.toJSON();
		var price = 0,prices = {},btw = 0,btws = {},totalOrderedItems = 0, self = this;
		Object.each(this.products, function (prodData,productID) {
			var prodPrice = prodData.data.price.toFloat(), prodtotalPrice = 0;
			var prodFormData = {};
			Object.each(prodData.data.optionData, function (optieInfo,optienaam) {
				Object.each(optieInfo, function (selectInfo,optievalue) {
					if (typeof prodFormData[selectInfo.type] == 'undefined') prodFormData[selectInfo.type] = {};
					if (typeof prodFormData[selectInfo.type][optienaam] == 'undefined') prodFormData[selectInfo.type][optienaam] = {};
					if (typeof data['shopdata['+productID+']['+selectInfo.identifier+']['+optievalue+']'] != 'undefined') {
						var value = data['shopdata['+productID+']['+selectInfo.identifier+']['+optievalue+']'];
						prodFormData[selectInfo.type][optienaam][optievalue] = {value:value,price:selectInfo.price};
					}
				});
			});
			Object.each(prodFormData.shopoptie, function (optieData,optienaam) {
				Object.each(optieData, function (valueData,optievalue) {
					if (valueData.value && valueData.price) {
						prodPrice += valueData.price.toFloat();
					}
				});
			});
			var orderedTotal = 0,orderedOpslag = 0;
			Object.each(prodFormData.shopaantal, function (optieData,optienaam) {
				Object.each(optieData, function (valueData,optievalue) {
					if (valueData.value.toInt() > 0) {
						orderedTotal += valueData.value.toInt();
						if (valueData.price) {
							orderedOpslag += (valueData.price.toFloat() * valueData.value.toInt());
						}
					}
				});
			});
			var fprodprice = prodPrice.toFixed(2).replace('.',',')
			self.productEls[productID].getElement('span.valuta').set('html','€ '+fprodprice);
			if (orderedTotal > 0) prodtotalPrice = prodPrice * orderedTotal + orderedOpslag;
			prices[productID] = prodtotalPrice;
			price += prodtotalPrice;
			totalOrderedItems += orderedTotal;
			
			var prodBtwFactor = self.options.btw[prodData.data.btw];
			var prodBtw = (prodtotalPrice/(100+prodBtwFactor))*prodBtwFactor;
			btws[productID] = prodBtw;
			btw += prodBtw;
	//console.log(prices,price);
		});
		var orderValid = true;
		var validMessage = '';
		//vervoerskosten
		if (price >  0) {
			if (price >  10) { //order meer dan 10
				var pcode = this.geo.adresFields.postcodeEl.get('value');
				var verzGebied = '';
	// console.log(pcode.match(/^[0-9]{4}[A-Z]{2}/));
				if (pcode != '' && pcode.match(/^[0-9]{4}[A-Z]{2}/)) { //adres niet valide
					bezorgKosten = 0;
					var codeNummers = pcode.substr(0,4).toInt();
					var enschedeBuiten = [7524,7525,7532];
					var UT = ['7522NB','7522ND','7522NH','7522LW','7522EA','7521NJ','7522LV','7522NJ','7522LP','7522LV','7522NM','7522NL','7522MJ','7522MG','7522NR','7522NE','7522MJ','7522NC','7522NR','7521AN','7521PA','7521AG','7547AN','7511GB','7521AG','7522NR','7514AE','7513EA','7522NH','7522ND','7522PB','7522NR','7522ND'];
					if (codeNummers >= 7500 && codeNummers <= 7547 && !enschedeBuiten.contains(codeNummers)) {// binnen enschede
						verzGebied = 'enschede';
						if (UT.contains(pcode)) { //ut
							verzGebied = 'utwente';
						}
						if (codeNummers == 7547) { //haven,planet,twence,marsstede
							verzGebied = 'utwente';
							if (pcode.match(/T/)) { //marssteden
								verzGebied = 'marssteden';
							}
						}
					} else { //buiten enschede
						if ((codeNummers > 7547 && codeNummers <= 7558) || enschedeBuiten.contains(codeNummers)) { //boekelo en hengelo en enschbuiten
							verzGebied = 'buiten';
						} else { //buiten bereik
							verzGebied = 'aanvraag';
							orderValid = false;
							validMessage = 'Adressen buiten verzorgingsgebied alleen op aanvraag!';
						}
					}
				console.log(verzGebied);
					this.verzgebiedSelect.set('value',verzGebied);
				} else {
					orderValid = false;
					validMessage = 'Adres is niet valide';
				}
			} else {
				orderValid = false;
				validMessage = 'Minimale afname bezorging €10,00';
			}
		} else {
			orderValid = false;
			validMessage = 'Selecteer eerst uw producten';
		}
		if (orderValid) { //kosten toevoegen
			this.form.getElement('button[type=submit]').set('disabled',false).set('html','Bevestigen');
			switch (verzGebied) {
				case 'utwente':
					bezorgKosten = 0;
				break;
				case 'marssteden':
					if (price < 20) {
						bezorgKosten = 9;
					}
				break;
				case 'enschede':
					if (price < 25) {
						bezorgKosten = 10;
					}
				break;
				case 'buiten':
					if (price < 50) {
						bezorgKosten = 18;
					}
				break;
				case 'aanvraag':
					//valt in !orderValid
				break;
			}
			validMessage = this.verzgebiedSelect.getElement('[value='+verzGebied+']').get('text');
			if (bezorgKosten > 0) validMessage += ' Kosten: € '+bezorgKosten+',-.'
			this.bezorginfoEl.removeClass('box-warning').addClass('box-info').set('html',validMessage);
			this.vervoersInfoInput.set('value',validMessage);
			price += bezorgKosten;
		} else {
			this.bezorginfoEl.removeClass('box-info').addClass('box-warning').set('html',validMessage);
			this.form.getElement('button[type=submit]').set('disabled',true).set('html','Bestelling niet compleet');
		}
		price = Math.round(price*100)/100;
		var fprice = price.toFixed(2).replace('.',',')
		document.id('prijsTotaal').set('html','€ '+fprice);
		btw = Math.round(btw*100)/100;
		var fbtw = btw.toFixed(2).replace('.',',')
		document.id('prijsBtw').set('html','€ '+fbtw);
		
		document.id('prijs').set('value',price);
		document.id('btw').set('value',btw);
	}
});
var bixZooShop = new bixZooShopClass();

var bixProduct = new Class({
	Implements: [Options],
	options: {
	},
	data: {},
	initialize: function (data,options) {
		this.setOptions(options);
		this.data = data;
	}
});

Element.implement({
    toJSON: function(){
        var json = {};
        this.getElements('input, select, textarea', true).each(function(el){
            if (!el.name || el.disabled || el.type == 'submit' || el.type == 'reset' || el.type == 'file') return;
            var value = (el.tagName.toLowerCase() == 'select') ? Element.getSelected(el).map(function(opt){
                return opt.value;
            }) : ((el.type == 'radio' || el.type == 'checkbox') && !el.checked) ? null : el.value;
            $splat(value).each(function(val){
                if (typeof val != 'undefined') {
                    json[el.name] = val;
                }
            });
        });
        return json;
    }
});


var bixGeo = new Class({
	Implements: [Options,Events],
	request: false,
	adresFields: {},
	userData: {},
	options: {
		reqUrl: '/pcode/pcodelookup.php',
		debug: false,
		onComplete: function () {}
	},
	initialize: function (control,options) {
		this.setOptions(options);
		try {
			var self = this;
			this.request = new Request.JSON({
				url:self.options.reqUrl,
				link:'cancel',
				onRequest: function(){},
				onFailure: function(error){},
				onError: function(text,error){},
				onSuccess: function(result){
					self.fillResult(result);
				}
			});
			$$('[name^='+control+']').each( function (inputEl) {
				if (inputEl.hasClass('postcode')) {self.adresFields.postcodeEl = inputEl;}
				if (inputEl.hasClass('huisnummer')) {self.adresFields.huisnummerEl = inputEl;}
				if (inputEl.hasClass('huisnummer_toevoeging')) {self.adresFields.huisnummertvEl = inputEl;}
				if (inputEl.hasClass('straat')) {self.adresFields.straatEl = inputEl;}
				if (inputEl.hasClass('plaats')) {self.adresFields.plaatsEl = inputEl;}
				if (inputEl.hasClass('land')) {self.adresFields.landEl = inputEl;}
				if (inputEl.hasClass('type')) {self.typeField = inputEl;}
				if (inputEl.hasClass('lat')) {self.latField = inputEl;}
				if (inputEl.hasClass('lon')) {self.lonField = inputEl;}
				if (inputEl.hasClass('postcode') || inputEl.hasClass('huisnummer') || inputEl.hasClass('huisnummer_toevoeging')) {
					inputEl.addEvent('keyup',function() {	
						self.lookup();
					});
				}
			});
			//parentholder zoeken
			this.adresbox = self.adresFields.postcodeEl.getParent('div.adresbox');
		} catch (e) {
		console.log(e);
			//ik hou hem lekker
		}
	},
	lookup: function () {
		var messageDiv = this.adresbox.getElement('div.box-warning');
		if (messageDiv) messageDiv.destroy();
		var postcode = this.adresFields.postcodeEl.get('value').replace(/\s+/, '').toUpperCase();
		if (postcode.length != 6) postcode = false;
		var huisnummer = this.adresFields.huisnummerEl.get('value').toInt();
		var huisnummer_toevoeging = this.adresFields.huisnummertvEl.get('value');
		if (this.adresFields.landEl.get('value') != 'NL' || !postcode || !huisnummer) return;
		this.adresbox.addClass('waiting');
		var req = {
			data: {
				postcode:postcode,
				huisnummer:huisnummer,
				huisnummer_toevoeging:huisnummer_toevoeging
			}
		};
		if (this.options.debug==true) if(console)console.log(req);
		this.request.post(Object.toQueryString(req))
	},
	fillResult: function (result) {
		if (typeof result.returnData.message != 'undefined') {
			this.adresbox.grab(new Element('div.box-warning',{text:result.returnData.message}));
		}
		if (typeof result.returnData.street != 'undefined') {
			this.adresFields.postcodeEl.set('value',result.returnData.postcode);
			this.adresFields.huisnummerEl.set('value',result.returnData.houseNumber);
			this.adresFields.huisnummertvEl.set('value',result.returnData.houseNumberAddition);
			this.adresFields.straatEl.set('value',result.returnData.street);
			this.adresFields.plaatsEl.set('value',result.returnData.city);
			this.typeField.set('value',result.returnData.addressType);
			this.latField.set('value',result.returnData.latitude);
			this.lonField.set('value',result.returnData.longitude);
		}
		if (this.options.debug==true) if(console)console.log(result.returnData);
		this.adresbox.removeClass('waiting');
		this.fireEvent('complete',result);
	}
});



