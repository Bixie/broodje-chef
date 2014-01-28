<?php
/**
* @package   com_zoo
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// init vars
$params = $item->getParams('site');

/* set media alignment */
$align = ($this->checkPosition('media')) ? $params->get('template.teaseritem_media_alignment') : '';

?>
<div class="grid-block productItem" id="product<?php echo $item->id; ?>">
	<div class="grid-box width60">
		<?php if ($this->checkPosition('title') || $this->checkPosition('meta')) : ?>
		<header>

			<?php if ($this->checkPosition('title')) : ?>
			<h3 class="title"><?php echo $this->renderPosition('title'); ?></h3>
			<?php endif; ?>

			<?php if ($this->checkPosition('subtitle')) : ?>
			<p class="pos-subtitle"><?php echo $this->renderPosition('subtitle'); ?></p>
			<?php endif; ?>

		</header>
		<?php endif; ?>
	</div>


	<div class="grid-box width20">
		<div class="content clearfix">

		<?php if ($this->checkPosition('content')) : ?>
		<div class="pos-content"><?php echo $this->renderPosition('content', array('style' => 'inline')); ?></div>
		<?php endif; ?>


		</div>
	</div>
	<div class="grid-box width20">

		<?php if ($this->checkPosition('form')) : ?>
		<div class="form">
			<?php echo $this->renderPosition('form', array('style' => 'inline')); ?>
		</div>
		<?php endif; ?>
		<?php if ($this->checkPosition('meta')) : ?>
		<p class="meta"><?php echo $this->renderPosition('meta'); ?></p>
		<?php endif; ?>
	</div>

</div>	