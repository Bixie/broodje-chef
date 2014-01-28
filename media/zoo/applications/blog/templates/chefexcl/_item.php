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

?>
<article class="item">
<?php if ($item) : ?>

	<?php if ($shopPage) : ?>
		<?php echo $this->renderer->render('item.shopform', array('view' => $this, 'item' => $item)); ?>
	<?php else: ?>
		<?php echo $this->renderer->render('item.teaser', array('view' => $this, 'item' => $item)); ?>
	<?php endif; ?>

<?php endif; ?>
</article>