<?php
/**
 *	COM_BIXPRINTSHOP - Online-PrintStore for Joomla
 *  Copyright (C) 2010-2012 Matthijs Alles
 *	Bixie.nl
 *
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class bixZooHelper.
 */
class bixZooHelper {

	/**
	 * @var
	 */
	protected static $_zoo;
	/**
	 * @var array
	 */
	protected static $_zooApplication = array();

	/**
	 * @param $item_id
	 * @return zooitemObj
	 */
	public static function getItem($item_id) {
		$zoo = self::zooApp();
		$item = $zoo->table->item->get($item_id);
		$application = self::zooApplication($item->application_id);
		$typeInfo = self::getTypeInfo($item,$application);
//pr($typeInfo,$item_id.'id');
		$zooroute = $zoo->route->item($item,false);
		$item->application_group = $application->application_group;
		$item->url = JRoute::_($zooroute.'&tmpl=component');
		$item->readmore = '<a href="'.$item->url.'" data-lightbox="type:iframe;width:800;height:600;}" class="float-right">'.JText::_('COM_BIXPRINTSHOP_PLG_WIKI_LEESMEER').'</a>';
		if ($item->getElements()) {
			$item->data = new stdClass;
			foreach ($item->getElements() as $element) {
				$element_id = $element->identifier;
				$elementInfo = $typeInfo->elements->$element_id;
				$name = $elementInfo->name;
				$item->data->$name = $element->get('value', '');
			}
		}
		return new zooitemObj($item);
	}

	/**
	 * @param $item
	 * @param $typeInfo
	 * @return array
	 */
	public static function getData($item,$typeInfo) {
		if ($item->getElements()) {
			$data = array();
			foreach ($item->getElements() as $element) {
				$element_id = $element->identifier;
				$elementInfo = $typeInfo->elements->$element_id;
				$data[$elementInfo->name] = $element->data();
			}
		}
		return $data;
	}

	/**
	 * @return App
	 */
	public static function zooApp() {
		if (!isset(self::$_zoo)) {
			// get ZOO app
			self::$_zoo = App::getInstance('zoo');
		}
		return self::$_zoo;
	}

	/**
	 * @param $app_id
	 * @return mixed
	 */
	public static function zooApplication($app_id) {
		$zoo = self::zooApp();
		if (!isset(self::$_zooApplication[$app_id])) {
			// get ZOO application
			self::$_zooApplication[$app_id] = $zoo->table->application->get($app_id);
		}
		return self::$_zooApplication[$app_id];
	}

	/**
	 * @param $item
	 * @param $application
	 * @return array|mixed
	 * @throws Exception
	 */
	public static function getTypeInfo($item,$application) {
 		$file = JPATH_ROOT.'/media/zoo/applications/'.$application->application_group.'/types/'.$item->type.'.config';
		if (file_exists($file)) {
			$json = file_get_contents($file);
			$data = json_decode($json);
			return $data;
		}
		JFactory::getApplication()->enqueueMessage('Fout in config file zootype ' . $item->type .'!','warning');
		return array();
	}

	/**
	 * @param     $text
	 * @param int $maxlength
	 * @return string
	 */
	public static function cleanDescr($text,$maxlength=250) {
		$textClear = JFilterOutput::cleanText($text);
		$textClear = strlen($text)>$maxlength?substr($text,0,($maxlength-3)).'...':$text;
		return $textClear;
	}
	
}

/**
 * Class zooitemObj
 */
class zooitemObj extends strictObj {
	/**
	 * @var
	 */
	public $name;
	/**
	 * @var
	 */
	public $text;
	/**
	 * @var
	 */
	public $url;
	/**
	 * @var
	 */
	public $type;
	/**
	 * @var
	 */
	public $readmore;
	/**
	 * @var
	 */
	public $attribName;
	/**
	 * @var
	 */
	public $optionValue;
	/**
	 * @var
	 */
	public $application_id;
	/**
	 * @var
	 */
	public $application_group;
	/**
	 * @var
	 */
	public $id;
	/**
	 * @var
	 */
	public $data;
	
}

/**
 * Class strictObj
 */
class strictObj extends JObject {
	/**
	 * @param null $data
	 */
	public function __construct($data=null) {
		if ($data) {
			foreach ($data as $k=>$v) {
				if (is_string($v) && preg_match('/^\{.*\}$/',$v) == true) {
					$registry = new JRegistry;
					$registry->loadString($v);
					$v = $registry->toArray();
				}
				$this->set($k,$v);
			}
		}
		unset($this->_errors);
	}

	/**
	 * @param string $key
	 * @param string $def_value
	 * @return string
	 */
	public function get($key,$def_value='') {
		$value = $this->$key;
		if (empty($value)) $value = $def_value;
		return $value;
	}

	/**
	 * @param string $key
	 * @param null   $value
	 * @return bool|mixed
	 */
	public function set($key,$value=null) {
		if (property_exists($this,$key))
			return parent::set($key,$value);
		return false;
	}
}

//base extensions
/**
 * @param        $var
 * @param string $string
 */
function print_r_pre($var,$string='') {
	if (empty($var)) echo 'Var is leeg!';
	if ($string) $string = $string.'<br/>';
	echo '<pre>'.$string;
	print_r($var);
	echo '</pre>';
}

/**
 * @param        $var
 * @param string $string
 */
function pr($var,$string='') {
	print_r_pre($var,$string);
}
