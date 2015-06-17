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

<div class="formHolder uk-form">
	<div class="bezorginfoHolder">
		<div id="bezorginfo" class="uk-alert"></div>
	</div>
	<div class="uk-grid" data-uk-margin data-uk-grid-match="{target:'.uk-panel'}">
		<div class="uk-width-medium-1-3 userForm adresbox">
			<div class="uk-panel bix-adres">
				<h3>Adresgegevens</h3>
				<div class="uk-form-row">
					<label class="uk-form-label" for="ufnaam">Naam*</label>
					<div class="uk-form-controls"><input type="text" id="ufnaam" name="userdata[naam]" value="<?php echo $naam; ?>" class="uk-width-1-1 required" size="20"/></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="ufemail">E-mail*</label>
					<div class="uk-form-controls"><input type="text" id="ufemail" name="userdata[email]" value="<?php echo $email; ?>" class="uk-width-1-1 validate-email required" size="20"/></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="uftelefoon">Telefoon*</label>
					<div class="uk-form-controls"><input type="text" id="uftelefoon" name="userdata[telefoon]" value="" class="uk-width-1-1 required" size="20"/></div>
				</div>
				<div class="uk-form-row">
					<div class="uk-grid">
						<div class="uk-width-1-3">
							<label class="uk-form-label" for="ufpostcode">Postcode*</label>
							<div class="uk-form-controls"><input type="text" id="ufpostcode" name="userdata[postcode]" value="" class="uk-width-1-1 required postcode" size="20"/></div>
						</div>
						<div class="uk-width-1-3">
							<label class="uk-form-label" for="ufhuisnummer">Huisnummer*</label>
							<div class="uk-form-controls"><input type="text" id="ufhuisnummer" name="userdata[huisnummer]" value="" class="uk-width-1-1 required huisnummer" size="20"/></div>
						</div>
						<div class="uk-width-1-3">
							<label class="uk-form-label" for="ufhuisnummer_toevoeging">Toevoeging</label>
							<div class="uk-form-controls"><input type="text" id="ufhuisnummer_toevoeging" name="userdata[huisnummer_toevoeging]" value="" class="uk-width-1-1 huisnummer_toevoeging" size="20"/></div>
						</div>
					</div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="ufadres">Straatnaam*</label>
					<div class="uk-form-controls"><input type="text" id="ufadres" name="userdata[straat]" value="" class="uk-width-1-1 required straat" size="20"/></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="ufplaats">Plaats*</label>
					<div class="uk-form-controls"><input type="text" id="ufplaats" name="userdata[plaats]" value="" class="uk-width-1-1 required plaats" size="20"/></div>
				</div>
				<input type="hidden" name="userdata[land]" value="NL" class="land"/>
				<input type="hidden" name="userdata[adrestype]" value="" class="type"/>
				<input type="hidden" name="vervoersInfo" value="" id="vervoersInfoInput"/>
				<input type="hidden" name="userdata[lat]" value="" class="lat"/>
				<input type="hidden" name="userdata[lon]" value="" class="lon"/>
			</div>
		</div>
		<div class="uk-width-medium-1-3 betalen">
			<div class="uk-panel">
				<h3>Bezorging en betaling</h3>
				<div class="uk-form-row">
					<label class="uk-form-label" for="leverdatum">Leverdatum</label>
					<div class="uk-form-controls">
						<div class="uk-form-icon">
							<i class="uk-icon-calendar"></i>
							<input type="" name="leverdatum" data-uk-datepicker="{minDate:'<?php echo JFactory::getDate()->format('d-m-Y'); ?>'}"/>
						</div>
					</div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="uur">Levertijd</label>
					<div class="uk-form-controls uk-grid">
						<div class="uk-width-1-2">
							<select name="uur" id="uur" class="uk-width-1-1" size="1">
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
						</div>
						<div class="uk-width-1-2">
							<select name="minuut" id="minuut" class="uk-width-1-1" size="1">
								<option value="00" selected="selected">00</option>
								<option value="15">15</option>
								<option value="30">30</option>
								<option value="45">45</option>
							</select>
						</div>
					</div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="betaalwijze">Betaalmethode</label>
					<div class="uk-form-controls uk-grid">
						<div class="uk-width-5-6">
							<select name="betaalwijze" id="betaalwijze" class="uk-width-1-1" size="1">
								<option value="iDEAL" selected="selected">iDEAL</option>
								<option value="Contant">Contant</option>
							<?php $disabled = in_array($rekeningGroup,$myGroups) ? '':' disabled="disabled"' ?>
								<option value="Op rekening"<?php echo $disabled; ?>>Op rekening</option>
							
							</select>
						</div>
						<div class="uk-width-1-6">
							<button class="uk-button uk-icon-button uk-icon-question" data-uk-tooltip title="Op rekening betalen? Meld u bovenaan deze pagina aan als vaste klant."></button>
						</div>
					</div>
				</div>
				<h3>Opmerkingen</h3>
				<textarea name="opmerking" class="uk-width-1-1 opmerking" rows="2" cols="20"></textarea>
			</div>
		</div>
		<div class="uk-width-medium-1-3 prijs">
			<div class="uk-panel uk-panel-box uk-panel-box-primary">
				<h3 class="uk-panel-title">Prijs</h3>
				<div class="uk-text-right prices">
					<h2 id="prijsTotaal">€ 0,00</h2>
					<span class="prijsBtw">(incl. <span id="prijsBtw">€ 0,00</span> btw)</span>
				</div>
				<h3>Bevestigen</h3>
				
				<div class="uk-form-row">
					<div class="uk-form-controls uk-form-controls-text">
						<label for="vrwdn">
							<input type="checkbox" name="vrwdn" id="vrwdn" value="1" class="required" />
							Ik heb de bestelvoorwaarden gelezen.
						</label>
					</div>
				</div>
				<input type="hidden" id="prijs" name="prijs" value="0">
				<input type="hidden" id="btw" name="btw" value="0">
				<button type="submit" class="uk-button uk-button-large uk-button-primary uk-width-1-1 uk-margin-top betalen">
					<i class="uk-icon-check uk-margin-small-right"></i>Bevestig bestelling</button>
			</div>
		</div>
	</div>
	<div class="uk-hidden">
		<select name="verzgebied" id="verzgebied" class="uk-width-1-1 required" size="1">
			<option value="">Maak een keuze</option>
			<option value="utwente">Business & Science Parc / Campus U-Twente / Havengebied & FC Twente Stadion (Enschede)</option>
			<option value="marssteden">Industriegebied Marssteden</option>
			<option value="enschede">Overige verzorgingsgebieden binnen Enschede</option>
			<option value="buiten">Verzorgingsgebieden buiten Enschede: Hengelo (Ov.), Lonneker, Boekelo, Buurse</option>
			<option value="aanvraag">Overige verzorgingsgebieden op aanvraag</option>
		</select>
	</div>
</div>
