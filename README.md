##Bixie Broodje Chef
==================

Bestelmodule voor ZOO

```
var verzGebied = '';
if (pcode != '' && pcode.match(/^[0-9]{4}[A-Z]{2}/)) { //adres niet valide
	bezorgKosten = 0;
	var codeNummers = pcode.substr(0,4).toInt();
	var enschedeBuiten = [7524,7525,7532];
	var UT = ['7522NB','7522ND','7522NH','7522LW','7522EA','7521NJ','7522LV','7522NJ','7522LP',
	'7522LV','7522NM','7522NL','7522MJ','7522MG','7522NR','7522NE','7522MJ','7522NC','7522NR',
	'7521AN','7521PA','7521AG','7547AN','7511GB','7521AG','7522NR','7514AE','7513EA','7522NH',
	'7522ND','7522PB','7522NR','7522ND'];
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

```