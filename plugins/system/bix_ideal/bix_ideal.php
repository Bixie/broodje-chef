<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use \Joomla\Registry\Registry as JRegistry;
use \Joomla\Utilities\ArrayHelper as JArrayHelper;

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
		$currentItemid = $app->input->getInt('Itemid',0);
		$bedanktItemid = $this->params->get('artikelItemid', 0);
		$orderID = uniqid('cc');
		$bestelDatum = strftime('%Y-%m-%d %T',time());
		if ($context != 'com_content.article' || ($currentItemid  != 0 && $currentItemid != $bedanktItemid)) {
			return true;
		}
		//request of return
		$shopdata = $app->input->get('shopdata',array(),'array');
		if (count($shopdata)) {
//echo '<pre>';
			$prodIDs = array();
			foreach ($shopdata as $prodIDstring => $prodData) {
				$prodIDs[] = intval(str_replace('product', '', $prodIDstring));
			}
			try {
				$db = JFactory::getDbo();
				$db->setQuery($db->getQuery(true)
					->select("id, name")
					->from("#__zoo_item")
					->where("id IN (" . implode(',', $prodIDs) . ")")
				);
				$prodTitels = $db->loadObjectList('id');
			} catch (RuntimeException $e) {
				return false;
			}
//print_r($shopdata);
//print_r($prodTitels);
			$html = array();
			$vervInfo = array();
			$html[] = '<p><h3>Besteloverzicht</h3>';
			$html[] = '<table border="0" cellpadding="5" cellspacing="0" width="100%" class="prodtable">';
			$html[] = '<tbody>';
			$i = 0;
			foreach ($shopdata as $prodIDstring=>$aantalElement) {
				$productID = intval(str_replace('product', '', $prodIDstring));
				$prodhtml = array();
				$prodaantal = 0;
				$productNaam = $prodTitels[$productID]->name;
				foreach ($aantalElement as $type=>$aantal) {
					if ($aantal == 0) continue;
					$prodhtml[] = '<span>'.ucfirst($type).':</span> <strong>'.$aantal.'</strong><br/>';
					$prodaantal += $aantal;
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
			$prijs = $app->input->post->getFloat('prijs',0);
			$btw = $app->input->post->getFloat('btw',0);
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
			$userdata = $app->input->post->get('userdata', array(), 'array');
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
			$opmerking = $app->input->post->getString('opmerking','-');
			$html[] = '<h3>Opmerkingen</h3><p>';
			$html[] = nl2br($opmerking);
			$html[] = '</p>';
			$vervoersInfo = $app->input->post->getString('vervoersInfo','');
			$leverdatum = $app->input->post->getString('leverdatum','');
			$leverdatumDate = JFactory::getDate($leverdatum);
			$uur = $app->input->post->getString('uur','');
			$minuut = $app->input->post->getString('minuut','');
			$html[] = '<strong>Verzorgingsgebied:</strong> <span>'.$vervoersInfo.'</span><br/>';
			$html[] = '<strong>Levertijd:</strong> <span>'.JHtml::_('date',$leverdatumDate->toSql(true),'DATE_FORMAT_LC3').', '.$uur.':'.$minuut.'</span><br/>';
			$betaalwijze = $app->input->post->getString('betaalwijze','');
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
			include(dirname(__FILE__) . '/tmpl/mail.php');

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
			
		} elseif ($app->input->post->getString('h','')) {
			$errorMsg = false;
			$this->idealForm = $app->getUserState('plg_bix_ideal.idealForm',array());
			$idealSuccess = false;
			if (isset($this->idealForm->idealURL)) {
				$hash = $app->input->post->getString('h','');
				$event = $app->input->post->getString('e',false);
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
		$html[] = "jQuery(function() {setTimeout(function () {document.idealForm.submit()},500);});";
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

	public function onAjaxBix_ideal () {
		$data = JFactory::getApplication()->input->get('data', array(), 'array');
// print_r($data) ;
		$helper = new PostcodeNl_Api_Helper_Data();
		return array(
			'response' => $helper->lookupAddress($data['postcode'], $data['huisnummer'], $data['huisnummer_toevoeging']));

	}
}



/*
Copyright (c) 2012, Postcode.nl B.V.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of
      conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice, this list
      of conditions and the following disclaimer in the documentation and/or other materials
      provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

class PostcodeNl_Api_Helper_Data {
	const API_TIMEOUT = 3;

	public function lookupAddress($postcode, $houseNumber, $houseNumberAddition)
	{
		$serviceUrl = 'https://api.postcode.nl';
		$serviceKey = '6sweluzZcaevmcWEFUGW7qYWE1wD9XPdjZLtoRvtVoO';
		$serviceSecret = 'fABWL3t88Et8QysjAUrBrfsYG1nZVjAoKEIdOkiiaMw';
		$serviceShowcase = false;
		$serviceDebug = false;


		if (!$serviceUrl || !$serviceKey || !$serviceSecret)
		{
			return array('message' => JText::_('COM_BIXPRINTSHOP_PLG_POSTCODEFILL_NOT_CONFIGURED'));
		}

		// Check for SSL support in CURL, if connecting to `https`
		if (substr($serviceUrl, 0, 8) == 'https://')
		{
			$curlVersion = curl_version();
			if (!($curlVersion['features'] & CURL_VERSION_SSL))
			{
				return array('message' => 'Cannot connect to Postcode.nl API: Server is missing SSL (https) support for CURL.');
			}
		}

		$url = $serviceUrl . '/rest/addresses/' . urlencode($postcode). '/'. urlencode($houseNumber) . '/'. urlencode($houseNumberAddition);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::API_TIMEOUT);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, $serviceKey .':'. $serviceSecret);
		curl_setopt($ch, CURLOPT_USERAGENT, 'BixiePrintshopPostcodenl_plugin');
		$jsonResponse = curl_exec($ch);
		$curlError = curl_error($ch);
		curl_close($ch);

		$response = json_decode($jsonResponse, true);

		$sendResponse = array();
		if ($serviceShowcase)
			$sendResponse['showcaseResponse'] = $response;

		if ($serviceDebug)
		{

			$sendResponse['debugInfo'] = array(
				'requestUrl' => $url,
				'rawResponse' => $jsonResponse,
				'parsedResponse' => $response,
				'curlError' => $curlError,
				'configuration' => array(
					'url' => $serviceUrl,
					'key' => $serviceKey,
					'secret' => substr($serviceSecret, 0, 6) .'[hidden]',
					'showcase' => $serviceShowcase,
					'debug' => $serviceDebug,
				)
			);
		}

		if (is_array($response) && isset($response['exceptionId']))
		{
			switch ($response['exceptionId'])
			{
				case 'PostcodeNl_Controller_Address_InvalidPostcodeException':
					$sendResponse['message'] = 'Postcode niet geldig!';
					$sendResponse['messageTarget'] = 'postcode';
					break;
				case 'PostcodeNl_Service_PostcodeAddress_AddressNotFoundException':
					$sendResponse['message'] = 'Postcode en huisnummercombinatie niet geldig!';
					$sendResponse['messageTarget'] = 'huisnummer';
					break;
				default:
					$sendResponse['message'] = 'Fout in opzoeken postcode!';
					$sendResponse['messageTarget'] = 'huisnummer';
					break;
			}
		}
		else if (is_array($response) && isset($response['postcode']))
		{
			$sendResponse = array_merge($sendResponse, $response);
		}
		else
		{
			$sendResponse['message'] = 'Postcode.nl niet beschikbaar';
			$sendResponse['messageTarget'] = 'huisnummer';
		}
		return $sendResponse;
	}
}
