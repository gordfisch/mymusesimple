<?php

/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$class = ' class="first"';

if (count($this->items[$this->parent->id]) > 0 && $this->maxLevelcat != 0) :
?>
<ul>
<?php foreach($this->items[$this->parent->id] as $id => $item) : 
$item_params = new JRegistry();
$item_params->loadString($item->params);
?>
	<?php
	if ($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) :
	if (!isset($this->items[$this->parent->id][$id + 1]))
	{
		$class = ' class="last"';
	}
	?>
	<li <?php echo $class; ?>>
	<?php $class = ''; ?>
		<span class="item-title"><a href="<?php echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($item->id));?>">
			<?php echo $this->escape($item->title); ?></a> 
			<?php if ($this->params->get('show_cat_num_articles_cat') == 1) :?>
			(<?php echo $this->_getProductCount($item); ?>)
		<?php endif; ?>
		</span>
		<?php if ($this->params->get('show_cat_subcat_image') && $item_params->get('image')): ?>
<div class="list_image">
<a href="<?php echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($item->id)); ?>"
><img src="<?php echo  $item_params->get('image'); ?>" 
		<?php if ($this->params->get('cat_subcat_image_height')) : ?>
style="height: <?php echo $this->params->get('cat_subcat_image_height'); ?>px"
		<?php endif; ?>
alt="<?php echo htmlspecialchars($item_params->get('image')); ?>" border="0" /></a></div>
<?php endif; ?>
		<?php if ($this->params->get('show_subcat_desc_cat') == 1) :?>
		<?php if ($item->description && $item->description != '') : ?>
		<?php if ($this->params->get('subcat_desc_cat_truncate')) : 
			$item->description = JHtmlString::truncate($item->description,$this->params->get('subcat_desc_cat_truncate'));
		 	$item->description = str_replace("...",'',$item->description);
		 	$item->description = preg_replace("~</p>$~",' ...</p>',$item->description);
		
		endif; ?>
		
			<div class="category-desc">
				<?php echo JHtml::_('content.prepare', $item->description, '', 'com_content.categories'); ?>
			</div>
		<?php endif; ?>
        <?php endif; ?>
		

		<?php if (count($item->getChildren()) > 0) :

			$this->items[$item->id] = $item->getChildren();
			$this->parent = $item;
			$this->maxLevelcat--;
			echo $this->loadTemplate('items');
			$this->parent = $item->getParent();
			$this->maxLevelcat++;
		endif; ?>

	</li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>
