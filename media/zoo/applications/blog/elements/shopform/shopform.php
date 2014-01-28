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
class ElementShopform extends ElementOption {

	public function hasValue($params = array()) {
		return true;
	}
	
	public function render($params = array()) {
		$typeInfo = bixZooHelper::getTypeInfo($this->_item,bixZooHelper::zooApplication($this->_item->application_id));
		$paramsReg = $this->app->data->create($params);
		$this->app->document->addScript('elements:shopform/shopform.js');
		$shopOptions = $this->getShopOpties(array('shopoptie','shopaantal'));
		$data = $this->data('data',array());
		$itemData = bixZooHelper::getData($this->_item,$typeInfo);
		$jsInfo = array();
		foreach ($shopOptions as $option) {
			$sel = $this->_item->getElement($option->identifier)->get('option');
			$optionInfo = array();
			foreach ($option->option as $selOption) {
				if (empty($sel) || !in_array($selOption->value,$sel)) continue;
				$optionInfo[$selOption->value]['type'] = $option->type;
				$optionInfo[$selOption->value]['identifier'] = $option->identifier;
				$optionInfo[$selOption->value]['text'] = $selOption->name;
				$optionInfo[$selOption->value]['value'] = $selOption->value;
				$optionInfo[$selOption->value]['price'] = $data[$option->name][$selOption->value];
			}
			$jsInfo[$option->name] = $optionInfo;
		}
//pr($itemData,'jsInfo');
//pr($jsInfo,'jsInfo');
		$price = $this->cleanFloatValue($itemData['Basisprijs']['valuta']);
		$btw = $itemData['BTW']['option'][0];
		
		$js = "bixZooShop.addProduct(".$this->_item->id.",{"
				."productID: ".$this->_item->id.","
				."price: ".$price.","
				."btw: '".$btw."',"
				."optionData: ".json_encode($jsInfo)
			."},".json_encode($params).");";

		return '<script>'.$js.'</script>';
	}
	
	public function cleanFloatValue($value) {
		if (preg_match('/[\d]{2,3}\.[\d]{3}$/',$value)) $value = str_replace('.','',$value);
		if (preg_match('/[\d]\,[\d]{2}$/',$value)) $value = str_replace(',','.',$value);
		if (preg_match('/[\d]{2,3}\.[\d]{3}\,[\d]{2}$/',$value)) $value = str_replace(',','.',str_replace('.','',$value));
		$value = floatval($value);
        return $value;
	}
	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit(){

		// init vars
		$options = $this->getShopOpties();
		$default = $this->config->get('default');
		if (count($options)) {

			// set default, if item is new
			if ($default != '' && $this->_item != null && $this->_item->id == 0) {
				$default = array($default);
			} else {
				$default = array();
			}

			$selected_options  = $this->get('option', $default);

			$i       = 0;
			$html    = array();
			if (count($options)) {
				$html[]  = '<div>';
				foreach ($options as $option) {
					$html[]  = '<div><h4>'.$option->name.'</h4></div>';
	//pr($this->get($option->name));
					if (isset($option->value['option'])) {
						foreach ($option->value['option'] as $value) {
							$name = $this->getControlName($option->name.']['.$value);
							$label = $option->optionXref[$value];
							$values = $this->get($option->name);
							$html[]  = '<div>€ <input type="text" name="'.$name.'" size="5" value="'.$values[$value].'" /><label>'.$label.'</label></div>';
						}
					}
				}
				$html[] = '</div>';
			}

			return implode("\n", $html);
		}

		return JText::_("There are no options to choose from.");
	}
	

	protected function getShopOpties($types=array('shopoptie','shopaantal')) {
		$typeInfo = bixZooHelper::getTypeInfo($this->_item,bixZooHelper::zooApplication($this->_item->application_id));
//pr($typeInfo);
		$shopOpties = array();
		if ($typeInfo) {
			foreach ($this->_item->getElements() as $element) {
				$element_id = $element->identifier;
				$elementInfo = $typeInfo->elements->$element_id;
				if (in_array($elementInfo->type,$types)) {
					$elementInfo->value = $element->data();
					$elementInfo->identifier = $element->identifier;
					$elementInfo->type = $element->getElementType();
					$shopOpties[] = $elementInfo;
					if (isset($elementInfo->option)) {
					$elementInfo->optionXref = array();
						foreach ($elementInfo->option as $option) {
							$elementInfo->optionXref[$option->value] = $option->name;
						}
					}
				}
			}
		}
		return $shopOpties;
	}

}