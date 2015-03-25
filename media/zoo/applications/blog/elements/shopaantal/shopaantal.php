<?php
/**
* @package   com_zoo
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// register ElementOption class
App::getInstance('zoo')->loader->register('ElementOption', 'elements:option/option.php');
App::getInstance('zoo')->loader->register('bixZooHelper', 'libraries:zoohelper.php');

/*
	Class: ElementCheckbox
		The checkbox element class
*/
class ElementShopaantal extends ElementOption {

	public function render($params = array()) {

		// init vars
		$params = $this->app->data->create($params);
		$selected_options  = $this->get('option', array());
		$typeInfo = bixZooHelper::getTypeInfo($this->_item,bixZooHelper::zooApplication($this->_item->application_id));
		if ($typeInfo) {
			foreach ($this->_item->getElements() as $element) {
				$element_id = $element->identifier;
				$elementInfo = $typeInfo->elements->$element_id;
				if ($this->identifier == $element->identifier) {
					$name = $elementInfo->name;
				}
				if ($elementInfo->type == 'shopform' && isset($name )) {
					$data = $element->data();
					$prices = $data[$name];
					break;
				}
			}
		}
		$html    = array('<div class="aantalholder uk-flex">');
		$options = $this->config->get('option', array());
		$count = count($selected_options);
		foreach ($options as $option) {
			if (in_array($option['value'], $selected_options)) {
				$title = $prices[$option['value']]>0?'Meerprijs per stuk: € '.number_format($prices[$option['value']],2,',','.'):'';
				$html[]  = '<div class="uk-width-1-'.$count.' aantal uk-flex uk-flex-middle uk-flex-right">';
				if ($params->get('show_label',0)) {
					$html[]  = '<div class="labelholder"><label title="'.$title.'">'.$option['name'].'</label></div>';
					$title = '';
				} else {
					$title = $option['name'].'. '.$title;
				}
				$html[] = '<div class="select"><input data-zoo-product-option="'.$option['value'].'"
								id="shopaantal-'.$this->_item->id.$option['value'].'" type="text"
								onfocus="if (this.value==\'0\')this.value=\'\'" onblur="if (this.value==\'\')this.value=\'0\'"
								class="shopaantal uk-form-small uk-text-right" size="3" title="'.$title.'" name="shopdata[product'.$this->_item->id.']['.$option['value'].']" value="0" />';
				if ($params->get('show_buttons',0)) {
					$html[]  = '<div class="plminbuttons uk-button-group">';
					$html[]  = '<button type="button" class="uk-button uk-button-mini uk-button-success butt plus" onclick="document.id(\'shopaantal-'.$this->_item->id.$option['value'].'\').set(\'value\',(document.id(\'shopaantal-'.$this->_item->id.$option['value'].'\').get(\'value\').toInt()+1))"><i class="uk-icon-plus"></i></button>';
					$html[]  = '<button type="button" class="uk-button uk-button-mini uk-button-danger butt min" onclick="if(document.id(\'shopaantal-'.$this->_item->id.$option['value'].'\').get(\'value\').toInt() > 0)document.id(\'shopaantal-'.$this->_item->id.$option['value'].'\').set(\'value\',(document.id(\'shopaantal-'.$this->_item->id.$option['value'].'\').get(\'value\').toInt()-1));"><i class="uk-icon-minus"></i></button>';
					$html[]  = '</div>';
				}
				$html[]  = '</div></div>';
			}
		}
		$html[] = '</div>';

		return implode($html);

	}

	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit(){

		// init vars
		$options_from_config = $this->config->get('option', array());
		$default			 = $this->config->get('default');

		if (count($options_from_config)) {

			// set default, if item is new
			if ($default != '' && $this->_item != null && $this->_item->id == 0) {
				$default = array($default);
			} else {
				$default = array();
			}

			$selected_options  = $this->get('option', $default);

			$i       = 0;
			$html    = array('<div>');
			foreach ($options_from_config as $option) {
				$name = $this->getControlName('option', true);
				$checked = in_array($option['value'], $selected_options) ? ' checked="checked"' : null;
				$html[]  = '<div><input id="'.$name.$i.'" type="checkbox" name="'.$name.'" value="'.$option['value'].'"'.$checked.' /><label for="'.$name.$i++.'">'.$option['name'].'</label></div>';
			}
			// workaround: if nothing is selected, the element is still being transfered
			$html[] = '<input type="hidden" name="'.$this->getControlName('check').'" value="1" />';
			$html[] = '</div>';

			return implode("\n", $html);
		}

		return JText::_("There are no options to choose from.");
	}

}