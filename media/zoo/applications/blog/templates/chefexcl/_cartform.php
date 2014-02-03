<?php
/**
* @package   com_zoo
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
$japp = JFactory::getApplication();
$jparams	= $japp->getParams();
$shopPage = stripos($jparams->get('pageclass_sfx',''),'shop') !== false;
// echo '<pre>';
// print_r($shopPage);
// echo '</pre>';
$user = JFactory::getUser();
$naam = $user->id?$user->name:'';
$email = $user->id?$user->email:'';
JHtml::_('behavior.formvalidation');

$myGroups = JAccess::getGroupsByUser($user->get('id'), true);
$rekeningGroup = $this->params->get('template.rekeningGroup', 0);
// if (count($myGroups) && $rekeningGroup) {
	
// }
// pr($myGroups);

?>

<div class="formHolder">
	<div class="bezorginfoHolder">
		<div id="bezorginfo"></div>
	</div>
	<div class="grid-block width100">
		<div class="grid-box width33 userForm adresbox">
			<h3>Adresgegevens</h3>
			<ul class="form">
				<li><label for="ufnaam">Naam*</label><input type="text" id="ufnaam" name="userdata[naam]" value="<?php echo $naam; ?>" class="inputbox required" size="20"/></li>
				<li><label for="ufemail">E-mail*</label><input type="text" id="ufemail" name="userdata[email]" value="<?php echo $email; ?>" class="inputbox validate-email required" size="20"/></li>
				<li><label for="uftelefoon">Telefoon*</label><input type="text" id="uftelefoon" name="userdata[telefoon]" value="" class="inputbox required" size="20"/></li>
				<li><label for="ufpostcode">Postcode*</label><input type="text" id="ufpostcode" name="userdata[postcode]" value="" class="inputbox required postcode" size="20"/></li>
				<li><label for="ufhuisnummer">Huisnummer*</label><input type="text" id="ufadres" name="userdata[huisnummer]" value="" class="inputbox required huisnummer" size="20"/></li>
				<li><label for="ufhuisnummer_toevoeging">Toevoeging</label><input type="text" id="ufadres" name="userdata[huisnummer_toevoeging]" value="" class="inputbox huisnummer_toevoeging" size="20"/></li>
				<li><label for="ufadres">Straatnaam*</label><input type="text" id="ufadres" name="userdata[straat]" value="" class="inputbox required straat" size="20"/></li>
				<li><label for="ufplaats">Plaats*</label><input type="text" id="ufplaats" name="userdata[plaats]" value="" class="inputbox required plaats" size="20"/></li>
			</ul>
			<input type="hidden" name="userdata[land]" value="NL" class="land"/>
			<input type="hidden" name="userdata[adrestype]" value="" class="type"/>
			<input type="hidden" name="vervoersInfo" value="" id="vervoersInfoInput"/>
			<input type="hidden" name="userdata[lat]" value="" class="lat"/>
			<input type="hidden" name="userdata[lon]" value="" class="lon"/>
		<?php
		 ?>
		</div>
		<div class="grid-box width33 betalen">
			<ul class="form">
				<li><label for="leverdatum">Leverdatum</label>
				<?php echo JHtml::_('calendar', date('Y-m-d'), 'leverdatum', 'leverdatum', '%Y-%m-%d', array('class'=>'inputbox calendar','size'=>9)); ?></li>
				<li><label for="uur">Levertijd</label>
				<select name="uur" id="uur" class="inputbox" size="1">
					<option value="09">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12" selected="selected">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
				</select>
				<select name="minuut" id="minuut" class="inputbox" size="1">
					<option value="00" selected="selected">00</option>
					<option value="15">15</option>
					<option value="30">30</option>
					<option value="45">45</option>
				</select>
				</li>	
				<li><label for="betaalwijze">Betaalmethode</label>
				<select name="betaalwijze" id="betaalwijze" class="inputbox" size="1">
					<option value="iDEAL" selected="selected">iDEAL</option>
					<option value="Contant">Contant</option>
				<?php if (in_array($rekeningGroup,$myGroups)) : ?>
					<option value="Op rekening">Op rekening</option>
				<?php endif; ?>
				</select>
				</li>	
			</ul>
			<input type="hidden" id="prijs" name="prijs" value="0">
			<input type="hidden" id="btw" name="btw" value="0">
			<h3>Opmerkingen</h3>
			<textarea name="opmerking" class="opmerking" rows="2" cols="20"></textarea>
		</div>
		<div class="grid-box width33 prijs">
			<h3>Prijs</h3>
			<div class="prices">
			<h2 id="prijsTotaal">€ 0,00</h2>
			<span class="prijsBtw">(incl. <span id="prijsBtw">€ 0,00</span> btw)</span>
			</div>
			<h3>Bevestigen</h3>
			<input type="checkbox" name="vrwdn" id="vrwdn" value="1" class="inputbox required" />
			<label for="vrwdn">Ik heb de bestelvoorwaarden gelezen.</label>
			<button type="submit" class="betalen">Bevestig bestelling</button>
			
		</div>
	</div>
	<div class="hidden">
		<select name="verzgebied" id="verzgebied" class="inputbox required" size="1">
			<option value="">Maak een keuze</option>
			<option value="utwente">Business & Science Parc / Campus U-Twente / Havengebied & FC Twente Stadion (Enschede)</option>
			<option value="marssteden">Industriegebied Marssteden</option>
			<option value="enschede">Overige verzorgingsgebieden binnen Enschede</option>
			<option value="buiten">Verzorgingsgebieden buiten Enschede: Hengelo (Ov.), Lonneker, Boekelo, Buurse</option>
			<option value="aanvraag">Overige verzorgingsgebieden op aanvraag</option>
		</select>
	</div>
</div>
