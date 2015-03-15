<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;


/**
 * Plugin class for redirect handling.
 *
 * @package		Joomla.Plugin
 * @subpackage	System.redirect
 */
class plgSystemBix_ideal extends JPlugin {
	
	protected $idealForm;
	
	public function onAfterInitialise () {
		if (JFactory::getApplication()->isSite() && !class_exists('JDocumentRendererMessage') && file_exists(dirname(__FILE__) . '/tmpl/message.php')) {
			require_once dirname(__FILE__) . '/tmpl/message.php';
		}
	}	
	public function onContentPrepare($context, &$article, &$params, $page = 0)	{
		$app = JFactory::getApplication();
		$currentItemid = JRequest::getInt('Itemid',0);
		$bedanktItemid = $this->params->get('artikelItemid', 0);
		$orderID = uniqid('cc');
		$bestelDatum = strftime('%Y-%m-%d %T',time());
		if ($context != 'com_content.article' || ($currentItemid  != 0 && $currentItemid != $bedanktItemid)) {
			return true;
		}
		//request of return
		$shopdata = JRequest::getVar('shopdata',array(),'post','array');
		if (count($shopdata)) {
//echo '<pre>'.$context;
//print_r(JRequest::get('POST'));
			$prodIDs = array_keys($shopdata);
			$db = JFactory::getDbo();
			$db->setQuery($db->getQuery(true)
				->select("id, name")
				->from("#__zoo_item")
				->where("id IN (".implode(',',$prodIDs).")")
			);
			$prodTitels = $db->loadObjectList('id');	
//print_r($shopdata);
			$html = array();
			$vervInfo = array();
			$html[] = '<p><h3>Besteloverzicht</h3>';
			$html[] = '<table border="0" cellpadding="5" cellspacing="0" width="100%" class="prodtable">';
			$html[] = '<tbody>';
			$i = 0;
			$k = 0;
			foreach ($shopdata as $productID=>$aantalElement) {
				$prodhtml = array();
				$prodaantal = 0;
				$productNaam = $prodTitels[$productID]->name;
				foreach ($aantalElement as $identifier=>$productAantal) {
					foreach ($productAantal as $type=>$aantal) {
						if ($aantal == 0) continue;
						$prodhtml[] = '<span>'.ucfirst($type).':</span> <strong>'.$aantal.'</strong><br/>';
						$prodaantal += $aantal;
					}
				}
				if ($prodaantal) {
					$k = $i%2;
					$html[] = '<tr class="row'.$k.'">';
					$html[] =	 '<td width="66%" class="mcnTextContent"><h5>'.$productNaam.'</h5></td>';
					$html[] = 	'<td width="33%" style="text-align:right" class="mcnTextContent">'.implode($prodhtml).'</td>';
					$html[] = '</tr>';
					$i++;
				}
			}
			//userdata
			$prijs = JRequest::getVar('prijs',0,'post');
			$btw = JRequest::getVar('btw',0,'post');
			$html[] = '<tr>';
			$html[] =	 '<td class="mcnTextContent">Netto totaal</td>';
			$html[] =	 '<td style="text-align:right;border-top:1px solid #ccc;" class="mcnTextContent">€ '.number_format(($prijs - $btw),2,',','.').'</td>';
			$html[] = '</tr>';
			$html[] = '<tr>';
			$html[] =	 '<td class="mcnTextContent">BTW bedrag</td>';
			$html[] =	 '<td style="text-align:right" class="mcnTextContent">€ '.number_format($btw,2,',','.').'</td>';
			$html[] = '</tr>';
			$html[] = '<tr>';
			$html[] =	 '<td class="mcnTextContent"><strong>Prijs inclusief BTW</strong></td>';
			$html[] =	 '<td style="text-align:right;border-top:2px solid #ccc;" class="mcnTextContent"><strong>€ '.number_format($prijs,2,',','.').'</strong></td>';
			$html[] = '</tr>';
			$html[] = '</tbody>';
			$html[] = '</table><br/>';
			$userdata = JRequest::getVar('userdata',array(),'post','array');
			$html[] = '<h3>Bezorggegevens</h3>';
			$userMail = 'admin@bixie.nl';
			foreach ($userdata as $key=>$value) {
				if (in_array($key,array('lat','lon','adrestype'))) {
					$vervInfo[] = '<span>'.ucfirst($key).':</span> <span>'.$value.'</span><br/>';
					continue;
				}
				$html[] = '<span>'.ucfirst(str_replace('_',' ',$key)).':</span> <span>'.$value.'</span><br/>';
				if ($key == 'email') $userMail = $value;
			}
			$opmerking = JRequest::getVar('opmerking','-','post');
			$html[] = '<h3>Opmerkingen</h3><p>';
			$html[] = nl2br($opmerking);
			$html[] = '</p>';
			$vervoersInfo = JRequest::getVar('vervoersInfo','','post');
			$leverdatum = JRequest::getVar('leverdatum','','post');
			$uur = JRequest::getVar('uur','','post');
			$minuut = JRequest::getVar('minuut','','post');
			$html[] = '<strong>Verzorgingsgebied:</strong> <span>'.$vervoersInfo.'</span><br/>';
			$html[] = '<strong>Levertijd:</strong> <span>'.JHtml::_('date',$leverdatum,'DATE_FORMAT_LC3').', '.$uur.':'.$minuut.'</span><br/>';
			$betaalwijze = JRequest::getVar('betaalwijze','','post');
			$html[] = '<strong>Besteldatum:</strong> <span>'.JHTML::_( 'date', $bestelDatum, "DATE_FORMAT_LC3" ).'</span><br/>';
			$html[] = '<strong>Betaalmethode:</strong> <span>'.$betaalwijze.'</span><br/>';
			$html[] = '<strong>Order ID:</strong> <span>'.$orderID.'</span><br/>';
			$html[] = '</p>';
			
			$bestellingHtml = implode($html);
			$article->text .= $bestellingHtml;
			$googleUrl = 'https://www.google.nl/maps/preview#!data=!4m20!3m19!1m5!1sColosseum+66%2C+7521+PT+Enschede!2s0x47b8122d5dc7a4d7%3A0xc13775d493b5f5a9!3m2!3d52.2386156!4d6.8353829!1m2!1s'.rawurlencode($userdata['straat'].' '.$userdata['huisnummer'].','.$userdata['plaats']).'!5e1!2e0!3m8!1m3!1d22199!2d'.$userdata['lon'].'!3d'.$userdata['lat'].'!3m2!1i1680!2i872!4f13.1&fid=0';		
			
			//mailen
			$emailCss = $this->params->get('mailCSS','');
			$emailCss = $emailCss?'<style type="text/css">'.$emailCss.'</style>':'';
			$config = JFactory::getConfig();
			$fromname	= $config->get('fromname');
			$mailfrom	= $config->get('mailfrom');
			$emailSubject = 'Uw bestelling bij '.$fromname;
			$emailBody = $emailCss.$article->text;
			//klantmail vanuit template
			
			$mailContent = '';
			ob_start();
			include(dirname(__FILE__).'/tmpl/mail.php');

			$mailContent = ob_get_clean();
			// if (!$this->params->def('mailTest', 0)) 
			$mailReturn = JFactory::getMailer()->sendMail($mailfrom, $fromname, $userMail, $emailSubject, $mailContent,1);
			//adminmails
			$emails = explode(',',$this->params->get('mailto',''));
			$emailBody = $emailCss.'<strong>Bestelling via de website:</strong> <br/>'.$bestellingHtml.'<br/>';
			$emailBody .= implode($vervInfo);
			$emailBody .= '<a href="'.$googleUrl.'">Routebeschrijving</a>';
			$emailBody .= '<br/><br/>Powered by Bixie magic!';
			foreach( $emails as $email ) {
			// $email = 'admin@bixie.nl';
				if (!$this->params->def('mailTest', 0)) $mailReturn = JFactory::getMailer()->sendMail($mailfrom, $fromname, $email, $emailSubject, $emailBody,1);
				// Check for an error.
				if ($mailReturn !== true && !$this->params->def('mailTest', 0)) {
					$this->setError(JText::_('Fout bij versturen mails'));
					return false;
				}
			}
			if ($betaalwijze == 'iDEAL') {
				//ideal voorbereiden
				$this->idealForm->idealURL = 'https://internetkassa.abnamro.nl/ncol/prod/orderstandard.asp';
				$this->idealForm->idealPSPID = $this->params->def('pspID', 'TESTiDEALEASY');
				$this->idealForm->orderID = $orderID;
				$this->idealForm->amount = $prijs * 100;
				$this->idealForm->comment = 'Bestelling '.$orderID.', '.$config->get('sitename');
				$this->idealForm->idealReturn = JRoute::_('index.php?Itemid='.$currentItemid);
				if ($this->params->def('idealTest', 0)) $this->idealForm->idealPSPID = 'TESTiDEALEASY';
				$article->text = $this->getForm(); // alleen form in article
				//in sessie
				$app->setUserState('plg_bix_ideal.bestellingHtml',$bestellingHtml);
				$app->setUserState('plg_bix_ideal.idealForm',$this->idealForm);
			}
			
		} elseif (JRequest::getVar('h','')) {
			$errorMsg = false;
			$this->idealForm = $app->getUserState('plg_bix_ideal.idealForm',array());
			if (isset($this->idealForm->idealURL)) {
				$hash = JRequest::getVar('h','');
				$event = JRequest::getVar('e',false);
				$idealSuccess = false;
				$checkHash = $this->getHash();
				if (!$event && $checkHash == $hash) {
					$idealSuccess = true;
				} else {
					$idealTransaction = new stdClass;
					if ($event) {
						$errorMsg = $this->errorMsg($event);
					} else {
						$errorMsg = 'De hash-code in de return-url is niet valide';
					}
				}
			}
			//afhandeling
			if ($idealSuccess) {
				$betaalMessage = 'Betaling geslaagd!';
				$betaalMessageStyle = '';
			} else {
				$betaalMessage = $errorMsg;
				$betaalMessageStyle = 'uk-alert-warning';
			}
			$html = '<div class="uk-alert '.$betaalMessageStyle.'">'.$betaalMessage.'</div>';
			$html .= $app->getUserState('plg_bix_ideal.bestellingHtml','');
			$article->text .= $html;
			
			$app->setUserState('plg_bix_ideal.bestellingHtml',null);
			$app->setUserState('plg_bix_ideal.idealForm',null);
		}
	//echo '</pre>'; 
	
		return true;
	}
	
	public function errorMsg($event) {
		$errors = array(
			'cancel'=>'Transactie geannuleerd door gebruiker',
			'decline'=>'Transactie geweigerd',
			'exception'=>'Er heeft zich een fout voorgedaan',
		);
		return $errors[$event];
	}

	public function getHash() {
		$string = 'MySecretWordofTodayisStiekum';
		foreach ($this->idealForm as $key=>$value) {
			if ($key == 'orderID') continue;
			$string .= $key.$value;
		}
		return md5($string);
	}

	protected function getForm() {
		$hash = $this->getHash();
		$html = array();
		$html[] = '<script>';
		$html[] = "window.addEvent('load', function() {setTimeout(function () {document.idealForm.submit()},500);});";
		$html[] = '</script>';
		$html[] = '<h5>U wordt doorgelinkt naar iDEAL...</h5>';
		$html[] = '<form method="post" action="'.$this->idealForm->idealURL.'" id="idealForm" name="idealForm" class="style">';
		$html[] = '<input type="hidden" name="PSPID" value="'.$this->idealForm->idealPSPID.'" />';
		$html[] = '<input type="hidden" name="orderID" value="'.$this->idealForm->orderID.'" />';
		$html[] = '<input type="hidden" name="amount" value="'.$this->idealForm->amount.'" />';
		$html[] = '<input type="hidden" name="COM" value="'.$this->idealForm->comment.'" />';
		$html[] = '<input type="hidden" name="cancelurl" value="'.JURI::root().$this->idealForm->idealReturn.'?e=cancel&h='.$hash.'">';
		$html[] = '<input type="hidden" name="declineurl" value="'.JURI::root().$this->idealForm->idealReturn.'?e=decline&h='.$hash.'">';
		$html[] = '<input type="hidden" name="exceptionurl" value="'.JURI::root().$this->idealForm->idealReturn.'?e=exception&h='.$hash.'">';
		$html[] = '<input type="hidden" name="accepturl" value="'.JURI::root().$this->idealForm->idealReturn.'?h='.$hash.'">';
		$html[] = '<input type="hidden" name="homeurl" value="'.JURI::root().'">';
		$html[] = '<input type="hidden" name="currency" value="EUR" />';
		$html[] = '<input type="hidden" name="language" value="NL_NL" />';
		$html[] = '<input type="hidden" name="PM" value="iDEAL" />';
		$html[] = '</form>';
		$html[] = '<a href="javascript:void(0)" onclick="document.idealForm.submit()">Klik hier als u niet wordt doorgelinkt</a>';
	
		return implode ("\n",$html);
	}

}
