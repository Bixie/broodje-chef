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
if ($shopPage) {
	$this->params->set('template.items_cols', 1);
}
// init vars
$i       = 0;
$columns = array();
$column  = 0;
$row     = 0;
$rows    = ceil(count($this->items) / $this->params->get('template.items_cols', 1));

$renderer		= $this->app->document->loadRenderer('module');


if ($shopPage) {
	$itemId = $this->params->get('template.artikelItemid', 0);
	$url = JRoute::_('index.php?Itemid='.$itemId);
	echo '<form class="short style form-validate" data-bix-shopform="{btw:hoog:21,laag:6}" id="bixZooShopform" method="post" action="'.$url.'">';
	$currentCategoryId = 0;
	$currentCategory = false;
	foreach ($this->items as $item) {
		if ($currentCategoryId != $item->getPrimaryCategoryId()) {
			$currentCategory = $item->getPrimaryCategory();
			$contents = '';
			foreach (JModuleHelper::getModules('zoocat-'.$currentCategory->alias) as $mod)  {
				$contents .= $renderer->render($mod, array('style'=>-2));
			}
			echo $contents;
			$currentCategoryId = $item->getPrimaryCategoryId();
		}
	//print_r($item->getPrimaryCategoryId());
		echo $this->partial('item', compact('item'));
	}
	echo $this->partial('cartform', compact('item'));
	echo '</form>';
} else {
	// create columns
	foreach ($this->items as $item) {

		if ($this->params->get('template.items_order')) {
			// order down
			if ($row >= $rows) {
				$column++;
				$row  = 0;
				$rows = ceil((count($this->items) - $i) / ($this->params->get('template.items_cols', 1) - $column));
			}
			$row++;
			$i++;
		} else {
			// order across
			$column = $i++ % $this->params->get('template.items_cols', 1);
		}

		if (!isset($columns[$column])) {
			$columns[$column] = '';
		}

		$columns[$column] .= $this->partial('item', compact('item'));
	}

	// render columns
	$count = count($columns);
	if ($count) {

		echo '<div class="items items-col-'.$count.' grid-block">';
		for ($j = 0; $j < $count; $j++) {
			echo '<div class="grid-box width'.intval(100 / $count).'">'.$columns[$j].'</div>';
		}
		echo '</div>';
	}
	
}
if ($shopPage) {
}

// render pagination
echo $this->partial('pagination');