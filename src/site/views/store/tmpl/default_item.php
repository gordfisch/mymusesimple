<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */

// no direct access
defined('_JEXEC') or die;

// Create a shortcut for params.
$params = &$this->item->params;
$canEdit	= $this->item->params->get('access-edit');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtmlBehavior::framework();
$lang = JFactory::getLanguage();
?>

<?php if ($this->item->state == 0) : ?>
<div class="system-unpublished">
<?php endif; ?>

<?php if ($params->get('store_show_title')) : ?>
	<div class="feature-title">
	<h3>
		<?php if ($params->get('store_link_titles') && $params->get('access-view')) : ?>
			<a href="<?php echo JRoute::_(MyMuseHelperRoute::getProductRoute($this->item->id, $this->item->catid, $lang->getTag())); ?>">
			<?php echo $this->escape($this->item->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($this->item->title); ?>
		<?php endif; ?>
	</h3>
	</div>
<?php endif; ?>

<?php if ($params->get('store_show_product_image') && $this->item->list_image): ?>
<div class="list_image">
<a href="<?php echo JRoute::_(MyMuseHelperRoute::getProductRoute($this->item->id, $this->item->catid, $lang->getTag())); ?>"
><img src="<?php echo $this->item->list_image; ?>" 
alt="<?php echo htmlspecialchars($this->item->list_image); ?>" border="0" 
<?php if ($params->get('store_product_image_height')) : ?>
style="height: <?php echo $params->get('store_product_image_height'); ?>px"
<?php endif; ?>
/></a></div>
<?php endif; ?>



<?php if (!$params->get('store_show_intro_text')) : ?>
	<?php echo $this->item->event->afterDisplayTitle; ?>
<?php endif; ?>

<?php echo $this->item->event->beforeDisplayContent; ?>


<?php if($params->get('store_show_intro_text')) :?>
<?php echo $this->item->introtext; ?>
<?php endif; ?>


<?php 
if ($params->get('store_show_readmore') && $this->item->readmore) :
	if ($params->get('access-view')) :
		$link = JRoute::_(MyMuseHelperRoute::getProductRoute($this->item->slug, $this->item->catid));
	else :
		$menu = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();
		$itemId = $active->id;
		$link1 = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
		$returnURL = JRoute::_(MyMuseHelperRoute::getProductRoute($this->item->slug, $this->item->catid));
		$link = new JURI($link1);
		$link->setVar('return', base64_encode($returnURL));
	endif;
?>
		<p class="readmore">
				<a href="<?php echo $link; ?>">
					<?php if (!$params->get('access-view')) :
						echo JText::_('MYMUSE_REGISTER_TO_READ_MORE');
					elseif ($readmore = $this->item->alternative_readmore) :
						echo $readmore;
						if ($params->get('store_show_readmore_title', 0) != 0) :
						    echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
						endif;
					elseif ($params->get('show_readmore_title', 0) == 0) :
						echo JText::sprintf('MYMUSE_READ_MORE_TITLE');
					else :
						echo JText::_('MYMUSE_READ_MORE').' ';
						echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
					endif; ?></a>
		</p>
<?php endif; ?>

<?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>

<div class="item-separator"></div>
<?php echo $this->item->event->afterDisplayContent; ?>
