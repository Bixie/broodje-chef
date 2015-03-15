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
<div class="uk-panel uk-margin-bottom productItem" id="product<?php echo $item->id; ?>">
	<div class="uk-grid">
		<div class="uk-width-1-1 uk-width-medium-3-5">
			<?php if ($this->checkPosition('title') || $this->checkPosition('meta')) : ?>
			<header>

				<?php if ($this->checkPosition('title')) : ?>
				<h3 class="uk-margin-remove"><?php echo $this->renderPosition('title'); ?></h3>
				<?php endif; ?>

				<?php if ($this->checkPosition('subtitle')) : ?>
				<p class="pos-subtitle"><?php echo $this->renderPosition('subtitle'); ?></p>
				<?php endif; ?>

			</header>
			<?php endif; ?>
		</div>


		<div class="uk-width-1-3 uk-width-medium-1-10">
			<div class="uk-clearfix">

			<?php if ($this->checkPosition('content')) : ?>
			<div class="pos-content"><?php echo $this->renderPosition('content', array('style' => 'inline')); ?></div>
			<?php endif; ?>


			</div>
		</div>
		<div class="uk-width-2-3 uk-width-medium-3-10">

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
</div>	
<hr/>