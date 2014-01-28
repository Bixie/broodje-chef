<?php
class ElementValuta extends Element implements iSubmittable {

	public function hasValue($params = array()) {
		$value = $this->_data->get('valuta');
		if (!empty($value) || $value === 0) {
			return true;
		}
		return false;
	}
    public function edit() {
		$this->app->document->addStylesheet('elements:valuta/assets/valuta.css');
		if ($this->_data->get('valuta')) {
			$val_clean = $this->cleanFloatValue($this->_data->get('valuta'));
		}
		$adminclass = (JFactory::getApplication()->isAdmin()?' val_admin':'');
		$html = array();
		$html[] = '<div class="valutasymb'.$adminclass.'">'.$this->_config->get('formvaluta').'</div>';
		$html[] = $this->app->html->_('control.text', 'elements['.$this->identifier.'][valuta]', $val_clean, 'class="valuta" size="20" maxlength="255"');
		return implode("\n", $html);
    }
	
	public function getSearchData($params = array()) {
		$value = $this->_data->get('valuta');
		return $value;
	}

	public function render($params = array()) {
		$params = $this->app->data->create($params);
		$this->app->document->addStylesheet('elements:valuta/assets/valuta.css');
		$value = $this->showFloat($this->_data->get('valuta'));
		$html = array();
		$html[] = '<span class="valuta '.$params->get('class_suffix','').'">'.$params->get('valuta').' '.$value;
		$html[] = '</span>';

		return implode("\n", $html);
	}
	
	public function renderSubmission($params = array()) {
        return $this->edit();
	}
	
	public function validateSubmission($value, $params) {
		$val_clean = '';
		if ($value->get('valuta')) {
			$val_clean = $this->cleanFloatValue($value->get('valuta'));
		}
		$value->set('valuta',$val_clean);
		return $value;
	}
	
	public function cleanFloatValue($value) {
		if (preg_match('/[\d]{2,3}\.[\d]{3}$/',$value)) $value = str_replace('.','',$value);
		if (preg_match('/[\d]\,[\d]{2}$/',$value)) $value = str_replace(',','.',$value);
		if (preg_match('/[\d]{2,3}\.[\d]{3}\,[\d]{2}$/',$value)) $value = str_replace(',','.',str_replace('.','',$value));
		$value = floatval($value);
        return $value;
	}
	
	public function showFloat($value) {
		$nrdigits = (intval($value)==$value?0:2);
		$suffix = (intval($value)==$value?',-':'');
		$value = number_format($value,$nrdigits,',','.');
        return $value.$suffix;
	}
	
}
?>