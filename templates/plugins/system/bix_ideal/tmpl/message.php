<?php
/* *
 *	Bixie Printshop
 *  message.php
 *	Created on 9-3-14 16:29
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */
 
// No direct access
defined('_JEXEC') or die;



/**
 * JDocument system message renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererMessage extends JDocumentRenderer
{
	/**
	 * Renders the error stack and returns the results as a string
	 *
	 * @param   string  $name     Not used.
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  Not used.
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 */
	public function render ($name, $params = array(), $content = null) {
		// Initialise variables.
		$buffer = null;
		$lists = null;

		// Get the message queue
		$messages = JFactory::getApplication()->getMessageQueue();

		// Build the sorted message list
		if (is_array($messages) && !empty($messages)) {
			foreach ($messages as $msg) {
				if (isset($msg['type']) && isset($msg['message'])) {
					$lists[$msg['type']][] = $msg['message'];
				}
			}
		}

		$types = array(
			'message' => 'primary',
			'info' => 'primary',
			'warning' => 'warning',
			'error' => 'danger',
			'danger' => 'danger',
			'success' => 'success'
		);

		$icons = array(
			'message' => 'uk-icon-info',
			'info' => 'uk-icon-info',
			'warning' => 'uk-icon-exclamation-circle',
			'error' => 'uk-icon-ban',
			'danger' => 'uk-icon-ban',
			'success' => 'uk-icon-check'
		);

		// Build the return string
		$buffer .= "\n<div id=\"system-message-container\">";

		// If messages exist render them
		if (is_array($lists)) {
			$buffer .= "\n<div id=\"system-message\">";
			foreach ($lists as $type => $msgs) {
				$ukType = $types[strtolower($type)] ? $types[strtolower($type)] : 'info';
				if (count($msgs)) {
					$buffer .= "\n<ul class=\"uk-list\">";

					foreach ($msgs as $msg) {
						$buffer .= "\n\t<li>";
						$buffer .= "\n\t\t<div class=\"uk-alert uk-alert-" . $ukType . "\" data-type=\"" . $ukType . "\"  data-uk-alert>";
						$buffer .= "\n\t\t".'<a href="" class="uk-alert-close uk-close"></a>';
						$buffer .= "\n\t\t<span class=\"text\"><i class=\"" . @$icons[strtolower($type)] . " uk-margin-left uk-margin-right\"></i>" . $msg . "</span>";
						$buffer .= "\n\t\t</div>";
						$buffer .= "\n\t</li>";
					}
					$buffer .= "\n</ul>";
				}
			}
			$buffer .= "\n</div>";
		}

		$buffer .= "\n</div>";
		return $buffer;
	}
}
